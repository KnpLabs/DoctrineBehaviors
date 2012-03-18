<?php

namespace Knp\DoctrineBehaviors\ORM\Timestampable;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events;

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
        return in_array('Knp\DoctrineBehaviors\ORM\Timestampable\Timestampable', $classMetadata->reflClass->getTraitNames());
    }

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata
        ];
    }
}
