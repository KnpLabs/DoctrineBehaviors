<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Timestampable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

final class TimestampableSubscriber extends AbstractSubscriber
{
    private $dbFieldType;

    /**
     * @var string
     */
    private $timestampableTrait;

    public function __construct(
        ClassAnalyzer $classAnalyzer,
        bool $isRecursive,
        string $timestampableTrait,
        $dbFieldType
    ) {
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

        if (! $this->isTimestampable($classMetadata)) {
            return;
        }

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

    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    private function isTimestampable(ClassMetadata $classMetadata): bool
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->timestampableTrait,
            $this->isRecursive
        );
    }
}
