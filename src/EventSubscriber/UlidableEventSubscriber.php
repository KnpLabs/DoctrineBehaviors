<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\Contract\Entity\UlidableInterface;

final class UlidableEventSubscriber implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if (! is_a($classMetadata->reflClass->getName(), UlidableInterface::class, true)) {
            return;
        }

        if ($classMetadata->hasField('ulid')) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => 'ulid',
            'type' => 'string',
            'nullable' => true,
        ]);
    }

    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getEntity();
        if (! $entity instanceof UlidableInterface) {
            return;
        }

        $entity->generateUlid();
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata, Events::prePersist];
    }
}
