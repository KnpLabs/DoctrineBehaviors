<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Events;

/**
 * Translatable Doctrine2 listener.
 *
 * Provides mapping for translatable entities and their translations.
 */
class TranslatableListener implements EventSubscriber
{
    /**
     * Adds mapping to the translatable and translations.
     *
     * @param LoadClassMetadataEventArgs $eventArgs The event arguments
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $traitNames    = $classMetadata->reflClass->getTraitNames();

        if (in_array('Knp\DoctrineBehaviors\ORM\Translatable\Translatable', $traitNames)) {
            $classMetadata->mapOneToMany([
                'fieldName'    => 'translations',
                'mappedBy'     => 'translatable',
                'cascade'      => ['persist', 'merge', 'remove'],
                'targetEntity' => $classMetadata->name.'Translation'
            ]);
        }

        if (in_array('Knp\DoctrineBehaviors\ORM\Translatable\Translation', $traitNames)) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'translatable',
                'inversedBy'   => 'translations',
                'targetEntity' => substr($classMetadata->name, 0, -11)
            ]);

            $classMetadata->setPrimaryTable([
                'uniqueConstraints' => [[
                    'name'    => $classMetadata->getTableName().'_unique_translation',
                    'columns' => ['translatable_id', 'locale' ]
                ]],
            ]);
        }
    }

    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }
}
