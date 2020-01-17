<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;

final class DefaultSluggableRepository
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function isSlugUniqueFor(SluggableInterface $sluggable, string $uniqueSlug): bool
    {
        $entityClass = get_class($sluggable);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->managerRegistry->getManagerForClass($entityClass);

        $queryBuilder = $entityManager->createQueryBuilder()
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
