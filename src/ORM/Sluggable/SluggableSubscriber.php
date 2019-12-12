<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Sluggable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;

final class SluggableSubscriber implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($this->shouldSkip($classMetadata)) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => 'slug',
            'type' => 'string',
            'nullable' => true,
        ]);
    }

    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->processLifecycleEventArgs($lifecycleEventArgs);
    }

    public function preUpdate(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->processLifecycleEventArgs($lifecycleEventArgs);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata, Events::prePersist, Events::preUpdate];
    }

    private function shouldSkip(ClassMetadataInfo $classMetadataInfo): bool
    {
        if (! is_a($classMetadataInfo->getName(), SluggableInterface::class, true)) {
            return true;
        }

        if ($classMetadataInfo->hasField('slug')) {
            return true;
        }

        return false;
    }

    private function processLifecycleEventArgs(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getEntity();
        if (! $entity instanceof SluggableInterface) {
            return;
        }

        $entity->generateSlug();
    }
}
