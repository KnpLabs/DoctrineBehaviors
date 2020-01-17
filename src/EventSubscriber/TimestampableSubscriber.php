<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;

final class TimestampableSubscriber implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if (! is_a($classMetadata->reflClass->getName(), TimestampableInterface::class, true)) {
            return;
        }

        $classMetadata->addLifecycleCallback('updateTimestamps', Events::prePersist);
        $classMetadata->addLifecycleCallback('updateTimestamps', Events::preUpdate);

        foreach (['createdAt', 'updatedAt'] as $field) {
            if (! $classMetadata->hasField($field)) {
                $classMetadata->mapField([
                    'fieldName' => $field,
                    'type' => $this->getFieldType($loadClassMetadataEventArgs->getEntityManager()),
                    'nullable' => true,
                ]);
            }
        }
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }

    private function getFieldType(EntityManagerInterface $entityManager): string
    {
        return $this->isPostgreSqlPlatform($entityManager) ? 'datetimetz' : 'datetime';
    }

    private function isPostgreSqlPlatform(EntityManagerInterface $entityManager): bool
    {
        /** @var Connection $connection */
        $connection = $entityManager->getConnection();

        return $connection->getDatabasePlatform() instanceof PostgreSqlPlatform;
    }
}
