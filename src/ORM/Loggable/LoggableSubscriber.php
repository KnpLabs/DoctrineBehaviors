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

namespace Knp\DoctrineBehaviors\ORM\Loggable;

use Doctrine\ORM\Event\LifecycleEventArgs;


use Doctrine\ORM\Events,
    Knp\DoctrineBehaviors\ORM\AbstractSubscriber,
    Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * LoggableSubscriber handle Loggable entites
 * Listens to lifecycle events
 */
class LoggableSubscriber extends AbstractSubscriber
{
    /**
     * @var callable
     */
    private $loggerCallable;

    /**
     * @param callable $classAnalyzer
     */
    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, callable $loggerCallable)
    {
        parent::__construct($classAnalyzer, $isRecursive);
        $this->loggerCallable = $loggerCallable;
    }

    public function postPersist(LifecycleEventArgs $eventArgs): void
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $message = $entity->getCreateLogMessage();
            $loggerCallable = $this->loggerCallable;
            $loggerCallable($message);
        }

        $this->logChangeSet($eventArgs);
    }

    public function postUpdate(LifecycleEventArgs $eventArgs): void
    {
        $this->logChangeSet($eventArgs);
    }

    /**
     * Logs entity changeset
     */
    public function logChangeSet(LifecycleEventArgs $eventArgs): void
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $entity = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $uow->computeChangeSet($classMetadata, $entity);
            $changeSet = $uow->getEntityChangeSet($entity);

            $message = $entity->getUpdateLogMessage($changeSet);
            $loggerCallable = $this->loggerCallable;
            $loggerCallable($message);
        }
    }

    public function preRemove(LifecycleEventArgs $eventArgs): void
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $message = $entity->getRemoveLogMessage();
            $loggerCallable = $this->loggerCallable;
            $loggerCallable($message);
        }
    }

    public function setLoggerCallable(callable $callable): void
    {
        $this->loggerCallable = $callable;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
        ];
    }

    /**
     * Checks if entity supports Loggable
     *
     * @param  ReflectionClass $reflClass
     * @return boolean
     */
    protected function isEntitySupported(\ReflectionClass $reflClass)
    {
        return $this->getClassAnalyzer()->hasTrait($reflClass, 'Knp\DoctrineBehaviors\Model\Loggable\Loggable', $this->isRecursive);
    }
}
