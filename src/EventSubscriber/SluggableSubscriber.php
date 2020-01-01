<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Repository\DefaultSluggableRepository;

final class SluggableSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    private const SLUG = 'slug';

    /**
     * @var DefaultSluggableRepository
     */
    private $defaultSluggableRepository;

    public function __construct(DefaultSluggableRepository $defaultSluggableRepository)
    {
        $this->defaultSluggableRepository = $defaultSluggableRepository;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($this->shouldSkip($classMetadata)) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => self::SLUG,
            'type' => 'string',
            'nullable' => true,
        ]);
    }

    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->processLifecycleEventArgs($lifecycleEventArgs);
    }

    public function preUpdate(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->processLifecycleEventArgs($lifecycleEventArgs);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata, Events::prePersist, Events::preUpdate];
    }

    private function shouldSkip(ClassMetadataInfo $classMetadataInfo): bool
    {
        if (! is_a($classMetadataInfo->getName(), SluggableInterface::class, true)) {
            return true;
        }
        return $classMetadataInfo->hasField(self::SLUG);
    }

    private function processLifecycleEventArgs(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getEntity();
        if (! $entity instanceof SluggableInterface) {
            return;
        }

        $entity->generateSlug();

        if ($entity->shouldGenerateUniqueSlugs()) {
            $this->generateUniqueSlugFor($entity);
        }
    }

    private function generateUniqueSlugFor(SluggableInterface $sluggable): void
    {
        $i = 0;
        $slug = $sluggable->getSlug();

        $uniqueSlug = $slug;

        while (! $this->defaultSluggableRepository->isSlugUniqueFor($sluggable, $uniqueSlug)) {
            $uniqueSlug = $slug . '-' . ++$i;
        }

        $sluggable->setSlug($uniqueSlug);
    }
}
