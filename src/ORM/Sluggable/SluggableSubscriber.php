<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Sluggable;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;

final class SluggableSubscriber extends AbstractSubscriber
{
    /**
     * @var string
     */
    private $sluggableTrait;

    public function __construct(bool $isRecursive, string $sluggableTrait)
    {
        parent::__construct($isRecursive);

        $this->sluggableTrait = $sluggableTrait;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            return;
        }

        if (! $this->isSluggable($classMetadata)) {
            return;
        }

        if ($classMetadata->hasField('slug')) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => 'slug',
            'type' => 'string',
            'nullable' => true,
        ]);
    }

    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getEntity();
        $entityManager = $lifecycleEventArgs->getEntityManager();
        $classMetadata = $entityManager->getClassMetadata(get_class($entity));

        if ($this->isSluggable($classMetadata)) {
            $entity->generateSlug();
        }
    }

    public function preUpdate(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getEntity();
        $entityManager = $lifecycleEventArgs->getEntityManager();
        $classMetadata = $entityManager->getClassMetadata(get_class($entity));

        if ($this->isSluggable($classMetadata)) {
            $entity->generateSlug();
        }
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata, Events::prePersist, Events::preUpdate];
    }

    private function isSluggable(ClassMetadata $classMetadata): bool
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->sluggableTrait,
            $this->isRecursive
        );
    }
}
