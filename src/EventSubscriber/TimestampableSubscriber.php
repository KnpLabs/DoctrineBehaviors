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
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        $className = $classMetadata->reflClass->getName();
        if (! is_a($className, TimestampableInterface::class, true)) {
            return;
        }

        $properties = $this->getTimestampableProperties($className);
        if (empty($properties)) {
            return;
        }

        $classMetadata->addLifecycleCallback('updateTimestamps', Events::prePersist);
        $classMetadata->addLifecycleCallback('updateTimestamps', Events::preUpdate);

        foreach ($properties as $field) {
            if (! $classMetadata->hasField($field)) {
                $classMetadata->mapField([
                    'fieldName' => $field,
                    'type' => $this->getFieldType(),
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

    /**
     * @return string[]
     */
    private function getTimestampableProperties(string $className): array
    {
        $properties = [];
        foreach (['getCreatedAtProperties', 'getUpdatedAtProperties'] as $method) {
            $callable = [$className, $method];
            if (is_callable($callable)) {
                // Merge additional properties to existing properties and ensure,
                // we have a non associative array as otherwise array_merge does not work as we want here.
                $additionalProperties = array_values(call_user_func($callable));
                $properties = array_values(array_unique(array_merge($properties, $additionalProperties)));
            }
        }

        return $properties;
    }

    private function getFieldType(): string
    {
        return $this->isPostgreSqlPlatform() ? 'datetimetz' : 'datetime';
    }

    private function isPostgreSqlPlatform(): bool
    {
        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();

        return $connection->getDatabasePlatform() instanceof PostgreSqlPlatform;
    }
}
