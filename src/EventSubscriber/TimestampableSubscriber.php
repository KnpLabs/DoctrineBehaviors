<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use ReflectionClass;

final class TimestampableSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    private $dateFieldType;

    public function __construct(string $timestampableDateFieldType)
    {
        $this->dateFieldType = $timestampableDateFieldType;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        /** @var ReflectionClass|null $reflectionClass */
        $reflectionClass = $classMetadata->getReflectionClass();
        if ($reflectionClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        $className = $reflectionClass->getName();
        if (! is_a($className, TimestampableInterface::class, true)) {
            return;
        }

        $properties = $this->getTimestampableProperties($classMetadata);
        if (empty($properties)) {
            return;
        }

        $classMetadata->addLifecycleCallback('updateTimestamps', Events::prePersist);
        $classMetadata->addLifecycleCallback('updateTimestamps', Events::preUpdate);

        foreach ($properties as $field) {
            if (! $classMetadata->hasField($field)) {
                $classMetadata->mapField([
                    'fieldName' => $field,
                    'type' => $this->dateFieldType,
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
     */
    private function getTimestampableProperties(ClassMetadata $classMetadata): array
    {
        $entity = $classMetadata->newInstance();
        if (! $entity instanceof TimestampableInterface) {
            return [];
        }

        // Use array_values to ensure a non-associative array so that array_merge works as we want it to.
        $createdAtProperties = array_values($entity->getCreatedAtProperties());
        $updatedAtProperties = array_values($entity->getUpdatedAtProperties());

        return array_unique(array_merge($createdAtProperties, $updatedAtProperties));
    }
}
