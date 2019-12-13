<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Tree;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\Contract\Model\Tree\NodeInterface;

final class TreeSubscriber implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if (! is_a($classMetadata->reflClass->getName(), NodeInterface::class, true)) {
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
