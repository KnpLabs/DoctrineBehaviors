<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Tree;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

final class TreeSubscriber extends AbstractSubscriber
{
    /**
     * @var string
     */
    private $nodeTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, bool $isRecursive, string $nodeTrait)
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

        if (! $this->isTreeNode($classMetadata)) {
            return;
        }

        if ($classMetadata->hasField('materializedPath')) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => 'materializedPath',
            'type' => 'string',
            'length' => 255,
        ]);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    private function isTreeNode(ClassMetadata $classMetadata): bool
    {
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, $this->nodeTrait, $this->isRecursive);
    }
}
