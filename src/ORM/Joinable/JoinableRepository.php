<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Joinable;

use Doctrine\ORM\QueryBuilder;

trait JoinableRepository
{
    public function getJoinAllQueryBuilder($alias = null, ?QueryBuilder $queryBuilder = null)
    {
        if ($alias === null) {
            $alias = $this->getAlias($this->getClassName());
        }

        if ($queryBuilder === null) {
            $queryBuilder = $this->createQueryBuilder($alias);
        }

        $className = $this->getClassName();

        $this->addJoinsToQueryBuilder($alias, $queryBuilder, $className);

        return $queryBuilder;
    }

    private function addJoinsToQueryBuilder($alias, QueryBuilder $queryBuilder, $className, $recursive = true): void
    {
        foreach ($this->getEntityManager()->getClassMetadata($className)->getAssociationMappings() as $assoc) {
            if (in_array(
                $assoc['targetEntity'],
                $queryBuilder->getRootEntities(),
                true
            ) || $className === $assoc['targetEntity']) {
                continue;
            }

            $uniqueJoinAlias = $this->getUniqueAlias($assoc['targetEntity'], $queryBuilder);
            $queryBuilder
                ->addSelect($uniqueJoinAlias)
                ->leftJoin(sprintf('%s.%s', $alias, $assoc['fieldName']), $uniqueJoinAlias)
            ;
            if ($recursive) {
                $this->addJoinsToQueryBuilder($uniqueJoinAlias, $queryBuilder, $assoc['targetEntity']);
            }
        }
    }

    private function getAlias($className)
    {
        $shortName = $this->getEntityManager()->getClassMetadata($className)->reflClass->getShortName();
        return strtolower(substr($shortName, 0, 1));
    }

    private function getUniqueAlias($className, QueryBuilder $queryBuilder)
    {
        $alias = $this->getAlias($className);

        $i = 1;
        $firstAlias = $alias;
        while ($this->aliasExists($alias, $queryBuilder)) {
            $alias = $firstAlias . $i;
            $i++;
        }

        return $alias;
    }

    private function aliasExists($alias, QueryBuilder $queryBuilder)
    {
        $aliases = [];
        foreach ($queryBuilder->getDqlPart('join') as $joins) {
            foreach ($joins as $join) {
                $aliases[] = $join->getAlias();
            }
        }

        $aliases[] = $queryBuilder->getRootAlias();

        return in_array($alias, $aliases, true);
    }
}
