<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Loggable;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\Model\Loggable\Loggable;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use ReflectionClass;

final class LoggableSubscriber extends AbstractSubscriber
{
    /**
     * @var callable
     */
    private $loggerCallable;

    public function __construct(bool $isRecursive, callable $loggerCallable)
    {
        parent::__construct($isRecursive);
        $this->loggerCallable = $loggerCallable;
    }

    public function postPersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entityManager = $lifecycleEventArgs->getEntityManager();
        $entity = $lifecycleEventArgs->getEntity();
        $classMetadata = $entityManager->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $message = $entity->getCreateLogMessage();
            $loggerCallable = $this->loggerCallable;
            $loggerCallable($message);
        }

        $this->logChangeSet($lifecycleEventArgs);
    }

    public function postUpdate(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->logChangeSet($lifecycleEventArgs);
    }

    /**
     * Logs entity changeset
     */
    public function logChangeSet(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entityManager = $lifecycleEventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $entity = $lifecycleEventArgs->getEntity();
        $classMetadata = $entityManager->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $unitOfWork->computeChangeSet($classMetadata, $entity);
            $changeSet = $unitOfWork->getEntityChangeSet($entity);

            $message = $entity->getUpdateLogMessage($changeSet);
            $loggerCallable = $this->loggerCallable;
            $loggerCallable($message);
        }
    }

    public function preRemove(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entityManager = $lifecycleEventArgs->getEntityManager();
        $entity = $lifecycleEventArgs->getEntity();
        $classMetadata = $entityManager->getClassMetadata(get_class($entity));

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

    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [Events::postPersist, Events::postUpdate, Events::preRemove];
    }

    protected function isEntitySupported(ReflectionClass $reflectionClass): bool
    {
        return $this->getClassAnalyzer()->hasTrait($reflectionClass, Loggable::class, $this->isRecursive);
    }
}
