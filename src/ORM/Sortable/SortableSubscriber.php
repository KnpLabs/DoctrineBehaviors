<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Sortable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

use Doctrine\ORM\Events;

use Doctrine\ORM\Mapping\ClassMetadata,
    Knp\DoctrineBehaviors\ORM\AbstractSubscriber,
    Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * Sortable subscriber.
 *
 * Adds mapping to the sortable entities.
 */
class SortableSubscriber extends AbstractSubscriber
{
    private $sortableTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $sortableTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->sortableTrait = $sortableTrait;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if ($classMetadata->reflClass === null) {
            return;
        }

        if ($this->isSortable($classMetadata)) {
            if (! $classMetadata->hasField('sort')) {
                $classMetadata->mapField([
                    'fieldName' => 'sort',
                    'type' => 'integer',
                ]);
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    /**
     * Checks if entity is a sortable
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function isSortable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->sortableTrait,
            $this->isRecursive
        );
    }
}
