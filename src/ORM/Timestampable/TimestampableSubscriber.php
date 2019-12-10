<?php

declare(strict_types=1);

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Timestampable;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

use Doctrine\ORM\Events;

use Doctrine\ORM\Mapping\ClassMetadata,
    Knp\DoctrineBehaviors\ORM\AbstractSubscriber,
    Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * Timestampable subscriber.
 *
 * Adds mapping to the timestampable entites.
 */
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

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

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
