<?php

namespace Knp\DoctrineBehaviors\ORM\Tree;

trait Tree
{
    public function getRootNodes($rootAlias = 't')
    {
        $class         = $this->getClassName();
        $pathSeparator = $class::getPathSeparator();

        $qb = $this->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias.'.path NOT LIKE :separator')
            ->setParameter('separator', $pathSeparator.'%')
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

    public function getTreeExceptNodeAndItsChildrenQB($entity, $rootAlias = 't')
    {
        return $this->getFlatTreeQB('', $rootAlias)
            ->andWhere($rootAlias.'.path NOT LIKE :except_path')
            ->setParameter('except_path', $entity->getPath().'%')
        ;
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
