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

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\OnFlushEventArgs,
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
        if ($this->isEntitySupported($classMetadata)) {
            $classMetadata->addLifecycleCallback('updateCreatedAt', Events::prePersist);
            $classMetadata->addLifecycleCallback('updateUpdatedAt', Events::prePersist);
            $classMetadata->addLifecycleCallback('updateUpdatedAt', Events::preUpdate);
        }
    }

    private function isEntitySupported(ClassMetadata $classMetadata)
    {
        $traitNames = $classMetadata->reflClass->getTraitNames();

        return in_array('Knp\DoctrineBehaviors\ORM\Timestampable\Timestampable', $traitNames);
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }
}
