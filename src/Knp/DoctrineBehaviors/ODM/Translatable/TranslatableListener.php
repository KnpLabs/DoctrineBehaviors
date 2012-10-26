<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ODM\Translatable;

use Knp\DoctrineBehaviors\Translatable\TranslatableListener as BaseTranslatableListener;

use Doctrine\ODM\MongoDB\Events;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Translatable Doctrine2 listener.
 *
 * Provides mapping for translatable documents and their translations.
 */
class TranslatableListener extends BaseTranslatableListener
{
    protected function mapTranslatable(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('translations')) {
            $classMetadata->mapManyEmbedded([
                'fieldName'      => 'translations',
                'targetDocument' => $classMetadata->name.'Translation',
                'strategy'       => 'pushAll',
            ]);
        }
    }

    protected function mapTranslation(ClassMetadata $classMetadata)
    {
    }

    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::postLoad,
        ];
    }
}
