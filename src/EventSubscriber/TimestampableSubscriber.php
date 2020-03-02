<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as MongoODMClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadata as ORMClassMetadata;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\EventSubscriber\AbstractEventSubscriber;

final class TimestampableSubscriber implements AbstractEventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        /** @var ORMClassMetadata|MongoODMClassMetadata */
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        $reflClass = $classMetadata->getReflectionClass();

        if ($reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if (! is_a($reflClass->getName(), TimestampableInterface::class, true)) {
            return;
        }

        if ($this->isORMObject($classMetadata)) {
            $classMetadata->addLifecycleCallback('updateTimestamps', Doctrine\ORM\Events::prePersist);
            $classMetadata->addLifecycleCallback('updateTimestamps', Doctrine\ORM\Events::preUpdate);
        }
        if ($this->isMongoODMObject($classMetadata))) {
            $classMetadata->addLifecycleCallback('updateTimestamps', Doctrine\ODM\MongoDB\Events::prePersist);
            $classMetadata->addLifecycleCallback('updateTimestamps', Doctrine\ODM\MongoDB\Events::preUpdate);
        }

        foreach (['createdAt', 'updatedAt'] as $field) {
            if (! $classMetadata->hasField($field) && null !== $fieldType = $this->getFieldType($classMetadata)) {
                $classMetadata->mapField([
                    'fieldName' => $field,
                    'type' => $fieldType,
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
        $subscribedEvents = [];

        if ($this->handlesORMEvents()) {
            $subscribedEvents[] = Doctrine\ORM\Events::loadClassMetadata;
        }
        if ($this->handlesMongoODMEvents()) {
            $subscribedEvents[] = Doctrine\ODM\MongoDB\Events::loadClassMetadata;
        }

        return $subscribedEvents;
    }

    private function getFieldType(ClassMetadata $classMetadata): ?string
    {
        if ($this->isORMObject($classMetadata)) {
            return Doctrine\DBAL\Types\Types::DATETIMETZ_IMMUTABLE;
        }
        if ($this->isMongoODMObject($classMetadata)) {
            return Doctrine\ODM\MongoDB\Types\Type::DATE;
        }

        return null;
    }
}
