<?php

namespace Knp\DoctrineBehaviors\ORM\Tree;

use Doctrine\ORM\QueryBuilder;

trait Tree
{
    /**
     * Returns all root nodes
     *
     * @api
     *
     * @param string $rootAlias
     *
     * @return array
     */
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

    /**
     * Returns a node hydrated with its children and parents
     *
     * @api
     *
     * @param string $path
     * @param string $rootAlias
     *
     * @return NodeInterface a node
     */
    public function getTree($path = '', $rootAlias = 't')
    {
        $results = $this->getFlatTree($path, $rootAlias);

        return $this->buildTree($results);
    }

    public function getTreeExceptNodeAndItsChildrenQB($entity, $rootAlias = 't')
    {
        return $this->getFlatTreeQB('', $rootAlias)
            ->andWhere($rootAlias.'.path NOT LIKE :except_path')
            ->setParameter('except_path', $entity->getPath().'%')
        ;
    }

    /**
     * Extracts the root node and constructs a tree using flat resultset
     *
     * @param Iterable|array $results a flat resultset
     *
     * @return NodeInterface
     */
    protected function buildTree($results)
    {
        if (!count($results)) {
            return;
        }

        $root = $results[0];
        $root->buildTree($results);

        return $root;
    }

    /**
     * Constructs a query builder to get a flat tree, starting from a given path
     *
     * @param string $path
     * @param string $rootAlias
     *
     * @return QueryBuilder
     */
    protected function getFlatTreeQB($path = '', $rootAlias = 't')
    {
        $qb = $this->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias.'.path LIKE :path')
            ->addOrderBy($rootAlias.'.path', 'ASC')
            ->setParameter('path', $path.'%')
        ;

        $this->addFlatTreeConditions($qb);

        return $qb;
    }

    /**
     * manipulates the lfat tree query builder before executing it.
     * Override this method to customize the tree query
     *
     * @param QueryBuilder $qb
     */
    protected function addFlatTreeConditions(QueryBuilder $qb)
    {
    }

    /**
     * Executes the flat tree query builder
     *
     * @return array the flat resultset
     */
    public function getFlatTree($path, $rootAlias = 't')
    {
        return $this
            ->getFlatTreeQB($path, $rootAlias)
            ->getQuery()
            ->execute()
        ;
    }
}
