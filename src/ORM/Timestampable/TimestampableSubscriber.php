<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Timestampable;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;

use Knp\DoctrineBehaviors\ORM\Trackable\TrackerInterface;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\ORM\Events,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Timestampable subscriber.
 *
 * Adds mapping to the timestampable entites.
 */
class TimestampableSubscriber extends AbstractSubscriber
{
    private $timestampableTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $timestampableTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->timestampableTrait  = $timestampableTrait;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isTimestampable($classMetadata->reflClass->name)) {
            foreach (['createdAt', 'updatedAt', 'deletedAt'] as $field) {
                if (!$classMetadata->hasField($field)) {
                    $classMetadata->mapField([
                       'fieldName' => $field,
                        'type'      => 'datetime',
                        'nullable'  => true,
                    ]);
                }
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    private function isTimestampable($class)
    {
        return $this->getClassAnalyzer()->hasTrait(new \ReflectionClass($class), $this->timestampableTrait, $this->isRecursive);
    }

    /**
     * Checks if entity is timestampable
     *
     * @param LifecycleEventArgs $classMetadata The event args
     *
     * @return Boolean
     */
    public function isEventSupported(LifecycleEventArgs $eventArgs)
    {
        return $this->isTimestampable(get_class($eventArgs->getEntity()));
    }

    public function getMetadata()
    {
        return new \DateTime;
    }

    public function getName()
    {
        return 'timestamp';
    }
}
