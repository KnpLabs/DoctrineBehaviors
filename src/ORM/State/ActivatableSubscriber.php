<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\State;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * Activable subscriber.
 *
 * Adds mapping to the activable entities.
 */
class ActivatableSubscriber extends AbstractSubscriber
{

    private $activatableTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $activatableTrait)
    {

        parent::__construct($classAnalyzer, $isRecursive);

        $this->activatableTrait = $activatableTrait;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }


        if ($this->isActivable($classMetadata)) {
            if (!$classMetadata->hasField('active')) {
                $classMetadata->mapField(array(
                    'fieldName' => 'active',
                    'type' => 'boolean',
                    'default' => true
                ));
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }


    /**
     * Checks if entity is active
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return Boolean
     */
    private function isActivable(ClassMetadata $classMetadata)
    {

        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->activatableTrait,
            $this->isRecursive
        );
    }
}
