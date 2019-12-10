<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Blameable;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;

final class BlameableSubscriber extends AbstractSubscriber
{
    /**
     * @var callable
     */
    private $userCallable;

    /**
     * userEntity name
     */
    private $userEntity;

    /**
     * @var string
     */
    private $blameableTrait;

    /**
     * @var mixed
     */
    private $user;

    /**
     * @param string $userEntity
     */
    public function __construct($isRecursive, $blameableTrait, ?callable $userCallable = null, $userEntity = null)
    {
        parent::__construct($isRecursive);

        $this->blameableTrait = $blameableTrait;
        $this->userCallable = $userCallable;
        $this->userEntity = $userEntity;
    }

    /**
     * Adds metadata about how to store user, either a string or an ManyToOne association on user entity
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if ($classMetadata->reflClass === null) {
            return;
        }

        if ($this->isBlameable($classMetadata)) {
            $this->mapEntity($classMetadata);
        }
    }

    /**
     * Stores the current user into createdBy and updatedBy properties
     */
    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entityManager = $lifecycleEventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $entity = $lifecycleEventArgs->getEntity();

        $classMetadata = $entityManager->getClassMetadata(get_class($entity));
        if ($this->isBlameable($classMetadata)) {
            if (! $entity->getCreatedBy()) {
                $user = $this->getUser();
                if ($this->isValidUser($user)) {
                    $entity->setCreatedBy($user);

                    $unitOfWork->propertyChanged($entity, 'createdBy', null, $user);
                    $unitOfWork->scheduleExtraUpdate($entity, [
                        'createdBy' => [null, $user],
                    ]);
                }
            }
            if (! $entity->getUpdatedBy()) {
                $user = $this->getUser();
                if ($this->isValidUser($user)) {
                    $entity->setUpdatedBy($user);
                    $unitOfWork->propertyChanged($entity, 'updatedBy', null, $user);

                    $unitOfWork->scheduleExtraUpdate($entity, [
                        'updatedBy' => [null, $user],
                    ]);
                }
            }
        }
    }

    /**
     * Stores the current user into updatedBy property
     */
    public function preUpdate(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entityManager = $lifecycleEventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $entity = $lifecycleEventArgs->getEntity();

        $classMetadata = $entityManager->getClassMetadata(get_class($entity));
        if ($this->isBlameable($classMetadata)) {
            if (! $entity->isBlameable()) {
                return;
            }
            $user = $this->getUser();
            if ($this->isValidUser($user)) {
                $oldValue = $entity->getUpdatedBy();
                $entity->setUpdatedBy($user);
                $unitOfWork->propertyChanged($entity, 'updatedBy', $oldValue, $user);

                $unitOfWork->scheduleExtraUpdate($entity, [
                    'updatedBy' => [$oldValue, $user],
                ]);
            }
        }
    }

    /**
     * Stores the current user into deletedBy property
     */
    public function preRemove(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entityManager = $lifecycleEventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $entity = $lifecycleEventArgs->getEntity();

        $classMetadata = $entityManager->getClassMetadata(get_class($entity));
        if ($this->isBlameable($classMetadata)) {
            if (! $entity->isBlameable()) {
                return;
            }
            $user = $this->getUser();
            if ($this->isValidUser($user)) {
                $oldValue = $entity->getDeletedBy();
                $entity->setDeletedBy($user);
                $unitOfWork->propertyChanged($entity, 'deletedBy', $oldValue, $user);

                $unitOfWork->scheduleExtraUpdate($entity, [
                    'deletedBy' => [$oldValue, $user],
                ]);
            }
        }
    }

    /**
     * set a custome representation of current user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * get current user, either if $this->user is present or from userCallable
     *
     * @return mixed The user reprensentation
     */
    public function getUser()
    {
        if ($this->user !== null) {
            return $this->user;
        }
        if ($this->userCallable === null) {
            return;
        }

        $callable = $this->userCallable;

        return $callable();
    }

    public function getSubscribedEvents()
    {
        return [Events::prePersist, Events::preUpdate, Events::preRemove, Events::loadClassMetadata];
    }

    public function setUserCallable(callable $callable): void
    {
        $this->userCallable = $callable;
    }

    private function isBlameable(ClassMetadata $classMetadata): bool
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->blameableTrait,
            $this->isRecursive
        );
    }

    private function mapEntity(ClassMetadata $classMetadata): void
    {
        if ($this->userEntity) {
            $this->mapManyToOneUser($classMetadata);
        } else {
            $this->mapStringUser($classMetadata);
        }
    }

    private function isValidUser($user): bool
    {
        if ($this->userEntity) {
            return $user instanceof $this->userEntity;
        }

        if (is_object($user)) {
            return method_exists($user, '__toString');
        }

        return is_string($user);
    }

    private function mapManyToOneUser(classMetadata $classMetadata): void
    {
        if (! $classMetadata->hasAssociation('createdBy')) {
            $classMetadata->mapManyToOne([
                'fieldName' => 'createdBy',
                'targetEntity' => $this->userEntity,
                'joinColumns' => [[
                    'onDelete' => 'SET NULL',
                ]],
            ]);
        }

        if (! $classMetadata->hasAssociation('updatedBy')) {
            $classMetadata->mapManyToOne([
                'fieldName' => 'updatedBy',
                'targetEntity' => $this->userEntity,
                'joinColumns' => [[
                    'onDelete' => 'SET NULL',
                ]],
            ]);
        }

        if (! $classMetadata->hasAssociation('deletedBy')) {
            $classMetadata->mapManyToOne([
                'fieldName' => 'deletedBy',
                'targetEntity' => $this->userEntity,
                'joinColumns' => [[
                    'onDelete' => 'SET NULL',
                ]],
            ]);
        }
    }

    private function mapStringUser(ClassMetadata $classMetadata): void
    {
        if (! $classMetadata->hasField('createdBy')) {
            $classMetadata->mapField([
                'fieldName' => 'createdBy',
                'type' => 'string',
                'nullable' => true,
            ]);
        }

        if (! $classMetadata->hasField('updatedBy')) {
            $classMetadata->mapField([
                'fieldName' => 'updatedBy',
                'type' => 'string',
                'nullable' => true,
            ]);
        }

        if (! $classMetadata->hasField('deletedBy')) {
            $classMetadata->mapField([
                'fieldName' => 'deletedBy',
                'type' => 'string',
                'nullable' => true,
            ]);
        }
    }
}
