<?php declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Repository;

use Doctrine\ORM\QueryBuilder;

interface QueryBuilderAwareRepositoryInterface
{
    /**
     * @param string $alias
     * @param string|null $indexBy
     */
    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder;
}
