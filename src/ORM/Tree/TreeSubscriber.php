<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Tree;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

class TreeSubscriber extends AbstractSubscriber
{
    private $nodeTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $nodeTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->nodeTrait = $nodeTrait;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if ($classMetadata->reflClass === null) {
            return;
        }

        if ($this->isTreeNode($classMetadata)) {
            if (! $classMetadata->hasField('materializedPath')) {
                $classMetadata->mapField([
                    'fieldName' => 'materializedPath',
                    'type' => 'string',
                    'length' => 255,
                ]);
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    /**
     * Checks if entity is a tree
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function isTreeNode(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, $this->nodeTrait, $this->isRecursive);
    }
}
