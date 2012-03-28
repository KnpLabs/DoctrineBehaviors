<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Timestampable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Events;

/**
 * Timestampable listener.
 *
 * Adds mapping to the timestampable entites.
 */
class TimestampableListener implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        if ($classMetadata->reflClass->hasMethod('updateTimestamps')) {
            $classMetadata->addLifecycleCallback('updateTimestamps', Events::prePersist);
            $classMetadata->addLifecycleCallback('updateTimestamps', Events::preUpdate);
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }
}
