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
use Knp\DoctrineBehaviors\Contract\Repository\SluggableRepositoryInterface;

final class SluggableSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    private const SLUG = 'slug';

    /**
     * @var SluggableRepositoryInterface
     */
    private $defaultSluggableRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        SluggableRepositoryInterface $defaultSluggableRepository
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

        $sluggableRepository = $this->entityManager->getRepository(get_class($sluggable));
        if (! $sluggableRepository instanceof SluggableRepositoryInterface) {
            $sluggableRepository = $this->defaultSluggableRepository;
        }

        while (! (
            $sluggableRepository->isSlugUniqueFor($sluggable, $uniqueSlug)
            && $this->isSlugUniqueInUnitOfWork($sluggableRepository, $sluggable, $uniqueSlug)
        )) {
            $uniqueSlug = $slug . '-' . ++$i;
        }

        $sluggable->setSlug($uniqueSlug);
    }

    private function isSlugUniqueInUnitOfWork(
        SluggableRepositoryInterface $sluggableRepository,
        SluggableInterface $sluggable,
        string $uniqueSlug
    ): bool {
        $scheduledEntities = $this->getOtherScheduledEntities($sluggable);
        foreach ($scheduledEntities as $entity) {
            if (! $sluggableRepository->isSlugUnique($uniqueSlug, $sluggable, $entity)) {
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
            $this->entityManager->getUnitOfWork()->getScheduledEntityInsertions(),
            $this->entityManager->getUnitOfWork()->getScheduledEntityUpdates(),
            $this->entityManager->getUnitOfWork()->getScheduledEntityDeletions()
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
