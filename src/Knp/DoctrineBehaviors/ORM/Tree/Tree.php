<?php

namespace Knp\DoctrineBehaviors\ORM\Tree;

trait Tree
{
    public function getRootNodes($rootAlias = 't', $pathSeparator = '/')
    {
        $qb = $this->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias.'.path NOT LIKE :path')
            ->setParameter('path', sprintf('%s%%s%', $pathSeparator, $pathSeparator))
        ;

        return $qb->getQuery()->execute();
    }

    public function getTree($path = '', $rootAlias = 't')
    {
        $results = $this->getFlatTree($path, $rootAlias);

        if (!count($results)) {
            return;
        }

        $root = $results[0];
        $root->buildTree($results);

        return $root;
    }

    public function getFlatTreeQB($path, $rootAlias = 't')
    {
        return $this->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias.'.path LIKE :path')
            ->addOrderBy($rootAlias.'.path', 'ASC')
            ->setParameter('path', $path.'%')
        ;
    }

    public function getFlatTree($path, $rootAlias = 't')
    {
        return $this
            ->getFlatTreeQB($path, $rootAlias)
            ->getQuery()
            ->execute()
        ;
    }
}
