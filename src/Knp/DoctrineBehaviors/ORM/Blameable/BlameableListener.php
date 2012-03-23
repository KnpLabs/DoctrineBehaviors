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

use Symfony\Component\Security\Core\SecurityContextInterface;

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
class BlameableListener implements EventSubscriber
{
    /**
     * @var callable
     */
    private $userCallable;

    /**
     * @var UserInterface|string
     */
    private $user;

    /**
     * userEntity name
     */
    private $userEntity;

    /**
     * map of already mapped entites
     *
     * @var array
     */
    private $map;

    /**
     * @constructor
     *
     * @param callable
     * @param string $userEntity
     */
    public function __construct(callable $userCallable = null, $userEntity = null)
    {
        $this->userCallable = $userCallable;
        $this->userEntity = $userEntity;
    }

    /**
     * Adds metadata about how to store user, either a string or an ManyToOne association on UserInterface entity
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        if ($this->isEntitySupported($classMetadata->reflClass) and !$this->isAlreadyMapped($classMetadata)) {
            $this->mapEntity($classMetadata);
        }
    }

    private function isAlreadyMapped(ClassMetadata $classMetadata)
    {
        return isset($this->maps[$classMetadata->reflClass->getName()]);
    }

    private function mapEntity(ClassMetadata $classMetadata)
    {
        if ($this->userEntity) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'createdBy',
                'targetEntity' => $this->userEntity,
            ]);
            $classMetadata->mapManyToOne([
                'fieldName'    => 'updatedBy',
                'targetEntity' => $this->userEntity,
            ]);
        }
        else {
            $classMetadata->mapField([
                'fieldName'  => 'createdBy',
                'type'       => 'string',
                'nullable'   => true,
            ]);

            $classMetadata->mapField([
                'fieldName'  => 'updatedBy',
                'type'       => 'string',
                'nullable'   => true,
            ]);
        }

        $this->map[$classMetadata->reflClass->getName()] = true;
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
        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $entity->setCreatedBy($this->getUser());
            $entity->setUpdatedBy($this->getUser());

            $uow->propertyChanged($entity, 'createdBy', null, $entity->getCreatedBy());
            $uow->propertyChanged($entity, 'updatedBy', null, $entity->getUpdatedBy());
            $uow->scheduleExtraUpdate($entity, [
                'createdBy' => [null, $entity->getCreatedBy()],
                'updatedBy' => [null, $entity->getUpdatedBy()],
            ]);
        }
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
        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $oldValue = $entity->getUpdatedBy();
            $entity->setUpdatedBy($this->getUser());

            $uow->propertyChanged($entity, 'updatedBy', null, $entity->getUpdatedBy());
            $uow->scheduleExtraUpdate($entity, [
                'updatedBy' => [$oldValue, $entity->getUpdatedBy()],
            ]);
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
     * @return boolean
     */
    private function isEntitySupported(\ReflectionClass $reflClass)
    {
        $isSupported = in_array('Knp\DoctrineBehaviors\ORM\Blameable\Blameable', $reflClass->getTraitNames());

        /*while(!$isSupported and $reflClass->getParentClass()) {
            $reflClass = $reflClass->getParentClass();
            $isSupported = $this->isEntitySupported($reflClass);
        }*/

        return $isSupported;
    }

    public function getSubscribedEvents()
    {
        $events = [
            Events::prePersist,
            Events::preUpdate,
            Events::loadClassMetadata,
        ];

        return $events;
    }
}
