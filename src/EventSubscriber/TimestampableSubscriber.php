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
use ReflectionClass;
use ReflectionException;

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
                    'nullable' => false,
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
     *
     * @throws ReflectionException
     */
    private function getTimestampableProperties(string $className): array
    {
        if (! class_exists($className)) {
            return [];
        }

        $reflectionClass = new ReflectionClass($className);
        $entity = $reflectionClass->newInstanceWithoutConstructor();
        if (! $entity instanceof TimestampableInterface) {
            return [];
        }

        // Use array_values to ensure a non-associative array so that array_merge works as we want it to.
        $createdAtProperties = array_values($entity->getCreatedAtProperties());
        $updatedAtProperties = array_values($entity->getUpdatedAtProperties());

        return array_unique(array_merge($createdAtProperties, $updatedAtProperties));
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
