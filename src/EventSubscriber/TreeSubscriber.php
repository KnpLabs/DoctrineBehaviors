<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;

final class TreeSubscriber implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if (! is_a($classMetadata->reflClass->getName(), TreeNodeInterface::class, true)) {
            return;
        }

        if ($classMetadata->hasField('materializedPath')) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => 'materializedPath',
            'type' => 'string',
            'length' => 255,
        ]);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata];
    }
}
