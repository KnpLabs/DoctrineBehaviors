<?php


declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Repository;

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Contract\Repository\SluggableRepositoryInterface;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SluggableWithUniqenessAndOwnRepositoryEntity;

final class SluggableWithUniqenessAndOwnRepositoryRepository extends EntityRepository implements SluggableRepositoryInterface
{
    public function isSlugUniqueFor(SluggableInterface $sluggable, string $uniqueSlug): bool
    {
        if ($sluggable instanceof SluggableWithUniqenessAndOwnRepositoryEntity) {
            $entityClass = get_class($sluggable);

            $queryBuilder = $this->getEntityManager()->createQueryBuilder()
                ->select('e')
                ->from($entityClass, 'e')
                ->select('COUNT(e)')
                ->andWhere('e.slug = :slug')
                ->andWhere('e.slugContext = :slugContext')
                ->setParameter('slug', $uniqueSlug)
                ->setParameter('slugContext', $sluggable->getSlugContext());

            $id = $sluggable->getId();
            if ($id !== null) {
                $queryBuilder
                    ->andWhere('e.id != :id')
                    ->setParameter('id', $id);
            }

            return ! (bool) $queryBuilder->getQuery()->getSingleScalarResult();
        }
        return true;
    }

    public function isSlugUnique(
        string $uniqueSlug,
        SluggableInterface $newOrUpdated,
        SluggableInterface $exisiting
    ): bool {
        if (! $newOrUpdated instanceof SluggableWithUniqenessAndOwnRepositoryEntity || ! $exisiting instanceof SluggableWithUniqenessAndOwnRepositoryEntity) {
            return true;
        }
        return ($newOrUpdated->getSlugContext() !== $exisiting->getSlugContext()) || ($uniqueSlug !== $exisiting->getSlug());
    }
}
