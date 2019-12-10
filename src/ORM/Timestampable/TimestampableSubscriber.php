<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Timestampable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

class TimestampableSubscriber extends AbstractSubscriber
{
    private $timestampableTrait;

    private $dbFieldType;

    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $timestampableTrait, $dbFieldType)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->timestampableTrait = $timestampableTrait;
        $this->dbFieldType = $dbFieldType;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if ($classMetadata->reflClass === null) {
            return;
        }

        if ($this->isTimestampable($classMetadata)) {
            if ($this->getClassAnalyzer()->hasMethod($classMetadata->reflClass, 'updateTimestamps')) {
                $classMetadata->addLifecycleCallback('updateTimestamps', Events::prePersist);
                $classMetadata->addLifecycleCallback('updateTimestamps', Events::preUpdate);
            }

            foreach (['createdAt', 'updatedAt'] as $field) {
                if (! $classMetadata->hasField($field)) {
                    $classMetadata->mapField([
                        'fieldName' => $field,
                        'type' => $this->dbFieldType,
                        'nullable' => true,
                    ]);
                }
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    /**
     * Checks if entity is timestampable
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function isTimestampable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->timestampableTrait,
            $this->isRecursive
        );
    }
}
