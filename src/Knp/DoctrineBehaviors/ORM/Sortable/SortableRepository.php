<?php

namespace Knp\DoctrineBehaviors\ORM\Sortable;

use Doctrine\ORM\QueryBuilder;

trait SortableRepository
{
    public function setSortPosition($entity, $position)
    {
        if ($entity->getSort() === $position) {
            return;
        }

        $qb = $this->createQueryBuilder('e')
            ->update($this->getEntityName(), 'e')
            ->set('e.sort', 'e.sort + 1')
            ->andWhere('e.sort >= :sort')
            ->setParameter('sort', $position)
        ;
        $entity->setSort($position);

        $this->addSortingScope($qb, $entity);

        $qb
            ->getQuery()
            ->execute()
        ;
    }

    protected function addSortingScope(QueryBuilder $qb, $entity)
    {
    }
}

