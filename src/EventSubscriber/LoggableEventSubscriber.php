<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs as BaseLifecycleEventArgs;
use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
final class LoggableEventSubscriber
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    public function postPersist(BaseLifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();
        if (! $entity instanceof LoggableInterface) {
            return;
        }

        $createLogMessage = $entity->getCreateLogMessage();
        $this->logger->log(LogLevel::INFO, $createLogMessage);

        $this->logChangeSet($lifecycleEventArgs);
    }

    public function postUpdate(BaseLifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();
        if (! $entity instanceof LoggableInterface) {
            return;
        }

        $this->logChangeSet($lifecycleEventArgs);
    }

    public function preRemove(BaseLifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();

        if ($entity instanceof LoggableInterface) {
            $this->logger->log(LogLevel::INFO, $entity->getRemoveLogMessage());
        }
    }

    /**
     * Logs entity changeset
     */
    private function logChangeSet(BaseLifecycleEventArgs $lifecycleEventArgs): void
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $entity = $lifecycleEventArgs->getObject();

        $entityClass = $entity::class;
        $classMetadata = $this->entityManager->getClassMetadata($entityClass);

        /** @var LoggableInterface $entity */
        $unitOfWork->computeChangeSet($classMetadata, $entity);
        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        $message = $entity->getUpdateLogMessage($changeSet);

        if ($message === '') {
            return;
        }

        $this->logger->log(LogLevel::INFO, $message);
    }
}
