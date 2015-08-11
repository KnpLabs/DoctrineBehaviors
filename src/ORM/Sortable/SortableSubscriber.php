<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Sortable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

/**
 * Sortable subscriber.
 *
 * Adds mapping to the sortable entities.
 */
class SortableSubscriber implements EventSubscriber
{
    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if (is_subclass_of($classMetadata->getName(), 'Knp\DoctrineBehaviors\Model\Sortable\SortableInterface')) {
            if (!$classMetadata->hasField('sort')) {
                $classMetadata->mapField(array(
                    'fieldName' => 'sort',
                    'type'      => 'integer',
                ));
            }
        }
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }
}
