<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;

final class DefaultSluggableRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function isSlugUniqueFor(SluggableInterface $sluggable, string $uniqueSlug): bool
    {
        $entityClass = get_class($sluggable);

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from($entityClass, 'e')
            ->select('COUNT(e)')
            ->andWhere('e.slug = :slug')
            ->setParameter('slug', $uniqueSlug);

        $id = $sluggable->getId();
        if ($id !== null) {
            $queryBuilder
                ->andWhere('e.id != :id')
                ->setParameter('id', $id);
        }

        return ! (bool) $queryBuilder->getQuery()
            ->getSingleScalarResult();
    }
}
