<?php

namespace Knp\DoctrineBehaviors\ORM\Translation;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Events;

class TranslatableListener implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        if (in_array('Knp\DoctrineBehaviors\ORM\Translation\Translatable', $classMetadata->reflClass->getTraitNames())) {
            $classMetadata->mapOneToMany([
                'targetEntity' => $classMetadata->name.'Translation',
                'fieldName' => 'translations',
                'mappedBy' => 'translatable'
            ]);
        }
        if (in_array('Knp\DoctrineBehaviors\ORM\Translation\Translation', $classMetadata->reflClass->getTraitNames())) {
            $classMetadata->mapManyToOne([
                // remove "Translation" suffix:
                'targetEntity' => substr($classMetadata->name, 0, -11),
                'fieldName' => 'translatable',
                'inversedBy' => 'translations'
            ]);
        }
    }

    public function getSubscribedEvents()
    {
        return array(Events::loadClassMetadata);
    }
}
