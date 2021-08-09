<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;

final class DefaultSluggableRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function isSlugUniqueFor(SluggableInterface $sluggable, string $uniqueSlug): bool
    {
        $entityClass = \get_class($sluggable);

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('COUNT(e)')
            ->from($entityClass, 'e')
            ->andWhere('e.slug = :slug')
            ->setParameter('slug', $uniqueSlug);

        $identifiers = $this->entityManager->getClassMetadata($entityClass)
            ->getIdentifierValues($sluggable);

        foreach ($identifiers as $field => $value) {
            if ($value === null || $field === 'slug') {
                continue;
            }

            $normalizedField = \str_replace('.', '_', $field);

            $queryBuilder
                ->andWhere(\sprintf('e.%s != :%s', $field, $normalizedField))
                ->setParameter($normalizedField, $value);
        }

        return ! (bool) $queryBuilder->getQuery()
            ->getSingleScalarResult();
    }
}
