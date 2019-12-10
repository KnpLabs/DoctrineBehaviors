<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Filterable;

use Doctrine\ORM\QueryBuilder;

trait FilterableRepository
{
    /**
     * Retrieve field which will be sorted using LIKE
     *
     * Example format: ['e:name', 'e:description']
     *
     * @return array
     */
    abstract public function getLikeFilterColumns();

    /**
     * Retrieve field which will be sorted using LOWER() LIKE
     *
     * Example format: ['e:name', 'e:description']
     *
     * @return array
     */
    abstract public function getILikeFilterColumns();

    /**
     * Retrieve field which will be sorted using EQUAL
     *
     * Example format: ['e:name', 'e:description']
     *
     * @return array
     */
    abstract public function getEqualFilterColumns();

    /**
     * Retrieve field which will be sorted using IN()
     *
     * Example format: ['e:group_id']
     *
     * @return array
     */
    abstract public function getInFilterColumns();

    /**
     * Filter values
     *
     * @param  array                      $filters - array like ['e:name' => 'nameValue'] where "e" is entity alias query, so we can filter using joins.
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    public function filterBy(array $filters, ?QueryBuilder $queryBuilder = null)
    {
        $filters = array_filter($filters, function ($filter) {
            return ! empty($filter);
        });

        if ($queryBuilder === null) {
            $queryBuilder = $this->createFilterQueryBuilder();
        }

        foreach ($filters as $col => $value) {
            foreach ($this->getColumnParameters($col) as $colName => $colParam) {
                $compare = $this->getWhereOperator($col) . 'Where';

                if (in_array($col, $this->getLikeFilterColumns(), true)) {
                    $queryBuilder
                        ->{$compare}(sprintf('%s LIKE :%s', $colName, $colParam))
                        ->setParameter($colParam, '%' . $value . '%')
                    ;
                }

                if (in_array($col, $this->getILikeFilterColumns(), true)) {
                    $queryBuilder
                        ->{$compare}(sprintf('LOWER(%s) LIKE :%s', $colName, $colParam))
                        ->setParameter($colParam, '%' . strtolower($value) . '%')
                    ;
                }

                if (in_array($col, $this->getEqualFilterColumns(), true)) {
                    $queryBuilder
                        ->{$compare}(sprintf('%s = :%s', $colName, $colParam))
                        ->setParameter($colParam, $value)
                    ;
                }

                if (in_array($col, $this->getInFilterColumns(), true)) {
                    $queryBuilder
                        ->{$compare}($queryBuilder->expr()->in(sprintf('%s', $colName), (array) $value))
                    ;
                }
            }
        }

        return $queryBuilder;
    }

    protected function getColumnParameters($col)
    {
        $colName = str_replace(':', '.', $col);
        $colParam = str_replace(':', '_', $col);

        return [$colName => $colParam];
    }

    protected function getWhereOperator($col)
    {
        return 'and';
    }

    protected function createFilterQueryBuilder()
    {
        return $this->createQueryBuilder('e');
    }
}
