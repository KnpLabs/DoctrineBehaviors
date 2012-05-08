<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Loggable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events;

/**
 * LoggableListener handle Loggable entites
 * Listens to lifecycle events
 */
class LoggableListener implements EventSubscriber
{
    /**
     * @var callable
     */
    private $loggerCallable;

    /**
     * @constructor
     *
     * @param callable
     */
    public function __construct(callable $loggerCallable)
    {
        $this->loggerCallable = $loggerCallable;
    }

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        return $this->logChangeSet($eventArgs);
    }

    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        return $this->logChangeSet($eventArgs);
    }

    /**
     * Logs entity changeset
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function logChangeSet(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $uow           = $em->getUnitOfWork();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $uow->computeChangeSet($classMetadata, $entity);
            $changeSet = $uow->getEntityChangeSet($entity);

            $message = $entity->getUpdateLogMessage($changeSet);
            $loggerCallable = $this->loggerCallable;
            $loggerCallable($message);
        }
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $message = $entity->getRemoveLogMessage();
            $loggerCallable = $this->loggerCallable;
            $loggerCallable($message);
        }
    }

    public function setLoggerCallable(callable $callable)
    {
        $this->loggerCallable = $callable;
    }

    /**
     * Checks if entity supports Loggable
     *
     * @param ReflectionClass $reflClass
     * @return boolean
     */
    private function isEntitySupported(\ReflectionClass $reflClass)
    {
        return in_array('Knp\DoctrineBehaviors\Model\Loggable\Loggable', $reflClass->getTraitNames());
    }

    public function getSubscribedEvents()
    {
        $events = [
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
        ];

        return $events;
    }
}
