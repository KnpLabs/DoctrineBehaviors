<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Sortable;

use Doctrine\ORM\QueryBuilder;

trait SortableRepositoryTrait
{
    public function reorderEntity($entity): void
    {
        if (! $entity->isReordered()) {
            return;
        }

        $qb = $this->createQueryBuilder('e')
            ->update($this->getEntityName(), 'e')
            ->set('e.sort', 'e.sort + 1')
            ->andWhere('e.sort >= :sort')
            ->setParameter('sort', $entity->getSort())
        ;
        $entity->setReordered();

        $this->addSortingScope($qb, $entity);

        $qb
            ->getQuery()
            ->execute()
        ;
    }

    protected function addSortingScope(QueryBuilder $queryBuilder, $entity): void
    {
    }
}
