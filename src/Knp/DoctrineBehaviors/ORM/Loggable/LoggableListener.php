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

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Knp\DoctrineBehaviors\ORM\AbstractListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events;

/**
 * LoggableListener handle Loggable entites
 * Listens to lifecycle events
 */
class LoggableListener extends AbstractListener
{
    /**
     * @var callable
     */
    private $loggerCallable;
    private $loggableTrait;

    /**
     * @param callable
     */
    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, callable $loggerCallable, $loggableTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);
        $this->loggerCallable = $loggerCallable;
        $this->loggableTrait  = $loggableTrait;
    }

    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isLoggable($classMetadata)) {
            $message = $entity->getCreateLogMessage();
            $loggerCallable = $this->loggerCallable;
            $loggerCallable($message);
        }

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

        if ($this->isLoggable($classMetadata)) {
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

        if ($this->isLoggable($classMetadata)) {
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
     * @param ClassMetadata $classMetadata The metadata
     * @return boolean
     */
    protected function isLoggable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, $this->loggableTrait, $this->isRecursive);
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
