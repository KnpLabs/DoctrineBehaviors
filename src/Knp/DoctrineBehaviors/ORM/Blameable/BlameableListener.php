<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Blameable;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Knp\DoctrineBehaviors\ORM\AbstractListener;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events;

/**
 * BlameableListener handle Blameable entites
 * Adds class metadata depending of user type (entity or string)
 * Listens to prePersist and PreUpdate lifecycle events
 */
class BlameableListener extends AbstractListener
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

    /**
     * @constructor
     *
     * @param callable
     * @param string $userEntity
     */
    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, callable $userCallable = null, $userEntity = null)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->userCallable = $userCallable;
        $this->userEntity = $userEntity;
    }

    /**
     * Adds metadata about how to store user, either a string or an ManyToOne association on user entity
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $this->mapEntity($classMetadata);
        }
    }

    private function mapEntity(ClassMetadata $classMetadata)
    {
        if ($this->userEntity) {
            $this->mapManyToOneUser($classMetadata);
        } else {
            $this->mapStringUser($classMetadata);
        }
    }

    private function mapStringUser(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasField('createdBy')) {
            $classMetadata->mapField([
                'fieldName'  => 'createdBy',
                'type'       => 'string',
                'nullable'   => true,
            ]);
        }

        if (!$classMetadata->hasField('updatedBy')) {
            $classMetadata->mapField([
                'fieldName'  => 'updatedBy',
                'type'       => 'string',
                'nullable'   => true,
            ]);
        }

        if (!$classMetadata->hasField('deletedBy')) {
            $classMetadata->mapField([
                'fieldName'  => 'deletedBy',
                'type'       => 'string',
                'nullable'   => true,
            ]);
        }
    }

    private function mapManyToOneUser(classMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('createdBy')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'createdBy',
                'targetEntity' => $this->userEntity,
            ]);
        }
        if (!$classMetadata->hasAssociation('updatedBy')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'updatedBy',
                'targetEntity' => $this->userEntity,
            ]);
        }
        if (!$classMetadata->hasAssociation('deletedBy')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'deletedBy',
                'targetEntity' => $this->userEntity,
            ]);
        }
    }

    /**
     * Stores the current user into createdBy and updatedBy properties
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em =$eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $entity = $eventArgs->getEntity();

        $classMetadata = $em->getClassMetadata(get_class($entity));
        if ($this->isEntitySupported($classMetadata->reflClass, true)) {
            if (!$entity->getCreatedBy()) {
                $user = $this->getUser();
                if ($this->isValidUser($user)) {
                    $entity->setCreatedBy($user);

                    $uow->propertyChanged($entity, 'createdBy', null, $user);
                    $uow->scheduleExtraUpdate($entity, [
                        'createdBy' => [null,  $user],
                    ]);
                }
            }
            if (!$entity->getUpdatedBy()) {
                $user = $this->getUser();
                if ($this->isValidUser($user)) {
                    $entity->setUpdatedBy($user);
                    $uow->propertyChanged($entity, 'updatedBy', null, $user);

                    $uow->scheduleExtraUpdate($entity, [
                        'updatedBy' => [null, $user],
                    ]);
                }
            }
            if (!$entity->getDeletedBy()) {
                $user = $this->getUser();
                if ($this->isValidUser($user)) {
                    $entity->setDeletedBy($user);
                    $uow->propertyChanged($entity, 'deletedBy', null, $user);

                    $uow->scheduleExtraUpdate($entity, [
                        'deletedBy' => [null, $user],
                    ]);
                }
            }
        }
    }

    /**
     *
     */
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
     * Stores the current user into updatedBy property
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $em =$eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $entity = $eventArgs->getEntity();

        $classMetadata = $em->getClassMetadata(get_class($entity));
        if ($this->isEntitySupported($classMetadata->reflClass, true)) {
            if (!$entity->isBlameable()) {
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
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $em =$eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $entity = $eventArgs->getEntity();

        $classMetadata = $em->getClassMetadata(get_class($entity));
        if ($this->isEntitySupported($classMetadata->reflClass, true)) {
            if (!$entity->isBlameable()) {
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
     *
     * @param mixed $user
     */
    public function setUser($user)
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
        if (null !== $this->user) {
            return $this->user;
        }
        if (null === $this->userCallable) {
            return;
        }

        $callable = $this->userCallable;

        return $callable();
    }

    /**
     * Checks if entity supports Blameable
     *
     * @param ClassMetadata $classMetadata
     * @param bool          $isRecursive   true to check for parent classes until trait is found
     *
     * @return boolean
     */
    private function isEntitySupported(\ReflectionClass $reflClass, $isRecursive = false)
    {
        return $this->getClassAnalyzer()->hasTrait($reflClass, 'Knp\DoctrineBehaviors\Model\Blameable\Blameable', $isRecursive)
            || $this->getClassAnalyzer()->hasTrait($reflClass, 'Knp\DoctrineBehaviors\Model\Blameable\BlameableMethods', $isRecursive)
        ;
    }

    public function getSubscribedEvents()
    {
        $events = [
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::loadClassMetadata,
        ];

        return $events;
    }

    public function setUserCallable(callable $callable)
    {
        $this->userCallable = $callable;
    }
}
