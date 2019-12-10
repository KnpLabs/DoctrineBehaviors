<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Sortable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

final class SortableSubscriber extends AbstractSubscriber
{
    /**
     * @var string
     */
    private $sortableTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, bool $isRecursive, string $sortableTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->sortableTrait = $sortableTrait;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if ($classMetadata->reflClass === null) {
            return;
        }

        if (! $this->isSortable($classMetadata)) {
            return;
        }

        if ($classMetadata->hasField('sort')) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => 'sort',
            'type' => 'integer',
        ]);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    private function isSortable(ClassMetadata $classMetadata): bool
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->sortableTrait,
            $this->isRecursive
        );
    }
}
