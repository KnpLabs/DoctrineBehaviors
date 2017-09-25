<?php

namespace Knp\DoctrineBehaviors\ORM\Tree;

use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\DoctrineBehaviors\Model\Tree\Node;

trait Tree
{
    /**
     * Constructs a query builder to get all root nodes
     *
     * @param string $rootAlias
     *
     * @return QueryBuilder
     */
    public function getRootNodesQB($rootAlias = 't')
    {
        return $this->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias.'.materializedPath = :empty')
            ->setParameter('empty', '')
        ;
    }

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
        return $this
            ->getRootNodesQB($rootAlias)
            ->getQuery()
            ->execute()
        ;
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
    public function getTree(
        $path = '',
        $rootAlias = 't',
        $materializedPathSeparator = null
    ) {
        $materializedPathSeparator = $materializedPathSeparator
            ?? self::getMaterializedPathSeparator()
        ;

        $results = $this->getFlatTree($path, $rootAlias, $materializedPathSeparator);

        return $this->buildTree($results);
    }

    public function getTreeExceptNodeAndItsChildrenQB(NodeInterface $entity, $rootAlias = 't', $materializedPathSeparator = null)
    {
        $materializedPathSeparator = $materializedPathSeparator
            ?? self::getMaterializedPathSeparator()
        ;

        $qb = $this->getFlatTreeQB('', $rootAlias, $materializedPathSeparator);

        return $qb
            ->andWhere($rootAlias.'.materializedPath NOT LIKE :except_exact_path')
            ->andWhere($rootAlias.'.materializedPath NOT LIKE :except_parent_path')
            ->andWhere($rootAlias.'.id != :id')
            ->setParameter('except_exact_path', $entity->getRealMaterializedPath())
            ->setParameter('except_parent_path', rtrim($entity->getRealMaterializedPath(), $materializedPath).$materializedPath.'%')
            ->setParameter('id', $entity->getId())
        ;
    }

    /**
     * Extracts the root node and constructs a tree using flat resultset
     *
     * @param Iterable|array $results a flat resultset
     *
     * @return NodeInterface
     */
    public function buildTree($results)
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
    public function getFlatTreeQB($path = '', $rootAlias = 't', $materializedPathSeparator = null)
    {
        $materializedPathSeparator = $materializedPathSeparator
            ?? self::getMaterializedPathSeparator()
        ;

        $qb = $this->createQueryBuilder($rootAlias);

        $qb
            ->andWhere(
                $qb->expr()->orX(
                    $rootAlias.'.materializedPath LIKE :exact_path',
                    $rootAlias.'.materializedPath LIKE :parent_path'
                )
            )
            ->addOrderBy($rootAlias.'.materializedPath', 'ASC')
            ->setParameter('exact_path', $path)
            ->setParameter(
                'parent_path',
                rtrim($path, $materializedPathSeparator).$materializedPathSeparator.'%'
            )
        ;

        $parentId = basename($path);
        if ($parentId) {
            $qb
                ->orWhere($rootAlias.'.id = :parent')
                ->setParameter('parent', $parentId)
            ;
        }

        $this->addFlatTreeConditions($qb);

        return $qb;
    }

    /**
     * manipulates the flat tree query builder before executing it.
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
    public function getFlatTree($path, $rootAlias = 't', $materializedPathSeparator = null)
    {
        $materializedPathSeparator = $materializedPathSeparator
            ?? self::getMaterializedPathSeparator()
        ;

        return $this
            ->getFlatTreeQB($path, $rootAlias, $materializedPathSeparator)
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Returns default materialized path separator
     *
     * @return string "/" by default
     */
    protected static function getMaterializedPathSeparator()
    {
        return '/';
    }
}
