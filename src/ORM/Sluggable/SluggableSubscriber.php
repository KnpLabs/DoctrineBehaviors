<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Sluggable;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

class SluggableSubscriber extends AbstractSubscriber
{
    private $sluggableTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $sluggableTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->sluggableTrait = $sluggableTrait;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if ($classMetadata->reflClass === null) {
            return;
        }

        if ($this->isSluggable($classMetadata)) {
            if (! $classMetadata->hasField('slug')) {
                $classMetadata->mapField([
                    'fieldName' => 'slug',
                    'type' => 'string',
                    'nullable' => true,
                ]);
            }
        }
    }

    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isSluggable($classMetadata)) {
            $entity->generateSlug();
        }
    }

    public function preUpdate(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isSluggable($classMetadata)) {
            $entity->generateSlug();
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata, Events::prePersist, Events::preUpdate];
    }

    /**
     * Checks if entity is sluggable
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function isSluggable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->sluggableTrait,
            $this->isRecursive
        );
    }
}
