<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Filterable;

/**
 * Filterable trait.
 *
 * Should be used inside entity repository, that needs to be filterable
 */
trait FilterableRepository
{
    /**
     * Retrieve field which will be sorted using LIKE
     *
     * Example format: ['e:name', 'e:description']
     *
     * @return array
     */
    public function getLikeFilterColumns()
    {
        return [];
    }

    /**
     * Retrieve field which will be sorted using EQUAL
     *
     * Example format: ['e:name', 'e:description']
     *
     * @return array
     */
    public function getEqualFilterColumns()
    {
        return [];
    }

    /**
     * @param array $filters - array like ['e.name' => 'nameValue']
     * @return Doctrine\DBAL\Query\QueryBuilder
     */
    public function filterBy(array $filters)
    {
        $filters = array_filter($filters, function($filter) {
            return !empty($filter);
        });

        $qb = $this->createFilterQueryBuilder();

        foreach ($filters as $col => $value) {
            $colName  = str_replace(':', '.', $col);
            $colParam = str_replace(':', '_', $col);

            if (in_array($col, $this->getLikeFilterColumns())) {
                $qb
                    ->andWhere(sprintf('%s LIKE :%s', $colName, $colParam))
                    ->setParameter($colParam, '%'.$value.'%')
                ;
            }

            if (in_array($col, $this->getEqualFilterColumns())) {
                $qb
                    ->andWhere(sprintf('%s = :%s', $colName, $colParam))
                    ->setParameter($colParam, $value)
                ;
            }
        }

        return $qb;
    }

    protected function createFilterQueryBuilder()
    {
        return $this->createQueryBuilder('e');
    }
}
