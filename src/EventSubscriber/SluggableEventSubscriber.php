<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Repository\DefaultSluggableRepository;

final class SluggableEventSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    private const SLUG = 'slug';

    /**
     * @var DefaultSluggableRepository
     */
    private $defaultSluggableRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        DefaultSluggableRepository $defaultSluggableRepository
    ) {
        $this->defaultSluggableRepository = $defaultSluggableRepository;
        $this->entityManager = $entityManager;
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

        while (! (
            $this->defaultSluggableRepository->isSlugUniqueFor($sluggable, $uniqueSlug)
            && $this->isSlugUniqueInUnitOfWork($sluggable, $uniqueSlug)
        )) {
            $uniqueSlug = $slug . '-' . ++$i;
        }

        $sluggable->setSlug($uniqueSlug);
    }

    private function isSlugUniqueInUnitOfWork(SluggableInterface $sluggable, string $uniqueSlug): bool
    {
        $scheduledEntities = $this->getOtherScheduledEntities($sluggable);
        foreach ($scheduledEntities as $entity) {
            if ($entity->getSlug() === $uniqueSlug) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return SluggableInterface[]
     */
    private function getOtherScheduledEntities(SluggableInterface $sluggable): array
    {
        $uowScheduledEntities = array_merge(
            $this->entityManager->getUnitOfWork()
                ->getScheduledEntityInsertions(),
            $this->entityManager->getUnitOfWork()
                ->getScheduledEntityUpdates(),
            $this->entityManager->getUnitOfWork()
                ->getScheduledEntityDeletions()
        );

        $scheduledEntities = [];
        foreach ($uowScheduledEntities as $entity) {
            if ($entity instanceof SluggableInterface && $sluggable !== $entity) {
                $scheduledEntities[] = $entity;
            }
        }

        return $scheduledEntities;
    }
}
