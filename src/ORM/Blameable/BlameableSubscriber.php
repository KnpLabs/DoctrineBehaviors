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

use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;

use Knp\DoctrineBehaviors\ORM\Trackable\TrackerInterface;

use Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Doctrine\Common\EventSubscriber;

use Doctrine\ORM\Events,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Event\LifecycleEventArgs,
    Doctrine\ORM\Event\LoadClassMetadataEventArgs;

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
     * @param callable
     * @param string $userEntity
     */
    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $blameableTrait, callable $userCallable = null, $userEntity = null)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->blameableTrait = $blameableTrait;
        $this->userCallable = $userCallable;
        $this->userEntity = $userEntity;
    }

    /**
     * Adds metadata about how to store user, either a string or an ManyToOne
     * association on user entity
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isBlameableClass($classMetadata->reflClass->name)) {
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
        foreach(['createdBy', 'updatedBy', 'deletedBy'] as $field) {
           if (!$classMetadata->hasField($field)) {
               $classMetadata->mapField([
                   'fieldName'  => $field,
                   'type'       => 'string',
                   'nullable'   => true,
               ]);
            }
        }
    }

    private function mapManyToOneUser(classMetadata $classMetadata)
    {
        foreach(['createdBy', 'updatedBy', 'deletedBy'] as $field) {
           if (!$classMetadata->hasField($field)) {
               $classMetadata->mapField([
                   'fieldName'  => $field,
                   'targetEntity' => $this->userEntity,
                   'joinColumns'  => [['onDelete' => 'SET NULL']],
               ]);
            }
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
            return null;
        }

        return call_user_func($this->userCallable);
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    public function setUserCallable(callable $callable)
    {
        $this->userCallable = $callable;
    }

    private function isBlameableClass($class)
    {
       return $this->getClassAnalyzer()->hasTrait(new \ReflectionClass($class), $this->blameableTrait, $this->isRecursive);
    }

    /**
     * Checks if entity is blameable
     *
     * @param LifecycleEventArgs $classMetadata The event args
     *
     * @return Boolean
     */
    public function isEntitySupported(LifecycleEventArgs $eventArgs)
    {
        return $this->isBlameableClass(get_class($eventArgs->getEntity()))
           && $eventArgs->getEntity()->isBlameable();
    }

    public function getMetadata()
    {
        $user = $this->getUser();

        return $this->isValidUser($user)
            ? $user
            : null;
    }

    public function getName()
    {
        return 'user';
    }
}
