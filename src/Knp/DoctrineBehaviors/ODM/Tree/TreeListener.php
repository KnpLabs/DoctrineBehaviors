<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ODM\Tree;

use Doctrine\ODM\MongoDB\Events;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;

class TreeListener implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isNode($classMetadata->reflClass)) {
            $this->mapChildren($classMetadata);
        }
    }

    private function isNode(\ReflectionClass $reflClass, $isRecursive = false)
    {
        return $reflClass->implementsInterface('Knp\DoctrineBehaviors\Model\Tree\NodeInterface');
    }

    protected function mapChildren(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('childNodes')) {
            $classMetadata->mapManyEmbedded([
                'fieldName'      => 'childNodes',
                'targetDocument' => $classMetadata->name,
                'strategy'       => 'set',
            ]);
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }
}
