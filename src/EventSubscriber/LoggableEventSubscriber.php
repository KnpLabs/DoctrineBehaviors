<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
final class LoggableEventSubscriber
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function postPersist(PostPersistEventArgs $postPersistEventArgs): void
    {
        $object = $postPersistEventArgs->getObject();
        if (! $object instanceof LoggableInterface) {
            return;
        }

        $createLogMessage = $object->getCreateLogMessage();
        $this->logger->log(LogLevel::INFO, $createLogMessage);

        $this->logChangeSet($postPersistEventArgs);
    }

    public function postUpdate(PostUpdateEventArgs $postUpdateEventArgs): void
    {
        $object = $postUpdateEventArgs->getObject();
        if (! $object instanceof LoggableInterface) {
            return;
        }

        $this->logChangeSet($postUpdateEventArgs);
    }

    public function preRemove(PreRemoveEventArgs $preRemoveEventArgs): void
    {
        $object = $preRemoveEventArgs->getObject();

        if ($object instanceof LoggableInterface) {
            $this->logger->log(LogLevel::INFO, $object->getRemoveLogMessage());
        }
    }

    /**
     * Logs entity changeset
     */
    private function logChangeSet(PostPersistEventArgs|PostUpdateEventArgs $lifecycleEventArgs): void
    {
        $entityManager = $lifecycleEventArgs->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $object = $lifecycleEventArgs->getObject();

        $entityClass = $object::class;
        $classMetadata = $entityManager->getClassMetadata($entityClass);

        /** @var LoggableInterface $object */
        $unitOfWork->computeChangeSet($classMetadata, $object);
        $changeSet = $unitOfWork->getEntityChangeSet($object);

        $message = $object->getUpdateLogMessage($changeSet);

        if ($message === '') {
            return;
        }

        $this->logger->log(LogLevel::INFO, $message);
    }
}
