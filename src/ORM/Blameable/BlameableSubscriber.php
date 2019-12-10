<?php

declare(strict_types=1);

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Blameable;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

use Doctrine\ORM\Events,
    Knp\DoctrineBehaviors\ORM\AbstractSubscriber,
    Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * BlameableSubscriber handle Blameable entites
 * Adds class metadata depending of user type (entity or string)
 * Listens to prePersist and PreUpdate lifecycle events
 */
class BlameableSubscriber extends AbstractSubscriber
{
    /**
     * @var callable
     */
    private $userCallable;

    /**
     * @var mixed
     */
    private $user;

    /**
     * userEntity name
     */
    private $userEntity;

    private $blameableTrait;

    /**
     * @param callable $classAnalyzer
     * @param string $userEntity
     */
    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $blameableTrait, ?callable $userCallable = null, $userEntity = null)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->blameableTrait = $blameableTrait;
        $this->userCallable = $userCallable;
        $this->userEntity = $userEntity;
    }

    /**
     * Adds metadata about how to store user, either a string or an ManyToOne association on user entity
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

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
    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $entity = $eventArgs->getEntity();

        $classMetadata = $em->getClassMetadata(get_class($entity));
        if ($this->isBlameable($classMetadata)) {
            if (! $entity->getCreatedBy()) {
                $user = $this->getUser();
                if ($this->isValidUser($user)) {
                    $entity->setCreatedBy($user);

                    $uow->propertyChanged($entity, 'createdBy', null, $user);
                    $uow->scheduleExtraUpdate($entity, [
                        'createdBy' => [null,  $user],
                    ]);
                }
            }
            if (! $entity->getUpdatedBy()) {
                $user = $this->getUser();
                if ($this->isValidUser($user)) {
                    $entity->setUpdatedBy($user);
                    $uow->propertyChanged($entity, 'updatedBy', null, $user);

                    $uow->scheduleExtraUpdate($entity, [
                        'updatedBy' => [null, $user],
                    ]);
                }
            }
        }
    }

    /**
     * Stores the current user into updatedBy property
     */
    public function preUpdate(LifecycleEventArgs $eventArgs): void
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $entity = $eventArgs->getEntity();

        $classMetadata = $em->getClassMetadata(get_class($entity));
        if ($this->isBlameable($classMetadata)) {
            if (! $entity->isBlameable()) {
                return;
            }
            $user = $this->getUser();
            if ($this->isValidUser($user)) {
                $oldValue = $entity->getUpdatedBy();
                $entity->setUpdatedBy($user);
                $uow->propertyChanged($entity, 'updatedBy', $oldValue, $user);

                $uow->scheduleExtraUpdate($entity, [
                    'updatedBy' => [$oldValue, $user],
                ]);
            }
        }
    }

    /**
     * Stores the current user into deletedBy property
     */
    public function preRemove(LifecycleEventArgs $eventArgs): void
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $entity = $eventArgs->getEntity();

        $classMetadata = $em->getClassMetadata(get_class($entity));
        if ($this->isBlameable($classMetadata)) {
            if (! $entity->isBlameable()) {
                return;
            }
            $user = $this->getUser();
            if ($this->isValidUser($user)) {
                $oldValue = $entity->getDeletedBy();
                $entity->setDeletedBy($user);
                $uow->propertyChanged($entity, 'deletedBy', $oldValue, $user);

                $uow->scheduleExtraUpdate($entity, [
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
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::loadClassMetadata,
        ];
    }

    public function setUserCallable(callable $callable): void
    {
        $this->userCallable = $callable;
    }

    private function mapEntity(ClassMetadata $classMetadata): void
    {
        if ($this->userEntity) {
            $this->mapManyToOneUser($classMetadata);
        } else {
            $this->mapStringUser($classMetadata);
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

    private function isValidUser($user)
    {
        if ($this->userEntity) {
            return $user instanceof $this->userEntity;
        }

        if (is_object($user)) {
            return method_exists($user, '__toString');
        }

        return is_string($user);
    }

    /**
     * Checks if entity is blameable
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function isBlameable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, $this->blameableTrait, $this->isRecursive);
    }
}
