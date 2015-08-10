<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Tree;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArg;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

/**
 * Tree subscriber.
 *
 * Adds mapping to the tree entities.
 */
class TreeSubscriber implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if (is_subclass_of($classMetadata->getName(), 'Knp\DoctrineBehaviors\Model\Tree\NodeInterface')) {
            if (!$classMetadata->hasField('childNodes')) {
                $classMetadata->mapOneToMany(array(
                    'targetEntity' => $classMetadata->getName(),
                    'fieldName'    => 'childNodes',
                    'mappedBy'     => 'parentNode',
                ));
            }
            if (!$classMetadata->hasField('parentNode')) {
                $classMetadata->mapManyToOne(array(
                    'targetEntity' => $classMetadata->getName(),
                    'fieldName'    => 'parentNode',
                    'inversedBy'   => 'childNodes',
                ));
            }
            if (!$classMetadata->hasField('materializedPath')) {
                $classMetadata->mapField(array(
                    'fieldName' => 'materializedPath',
                    'type'      => 'string',
                    'length'    => 255,
                ));
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }
}
