<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Tree;

use ArrayAccess;
use Doctrine\ORM\QueryBuilder;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;

trait TreeTrait
{
    /**
     * Constructs a query builder to get all root nodes
     */
    public function getRootNodesQB(string $rootAlias = 't'): QueryBuilder
    {
        return $this->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias . '.materializedPath = :empty')
            ->setParameter('empty', '');
    }

    public function getRootNodes(string $rootAlias = 't'): array
    {
        return $this->getRootNodesQB($rootAlias)
            ->getQuery()
            ->execute();
    }

    /**
     * Returns a node hydrated with its children and parents
     *
     * @return TreeNodeInterface[]|ArrayAccess|null
     */
    public function getTree(string $path = '', string $rootAlias = 't', array $extraParams = [])
    {
        $results = $this->getFlatTree($path, $rootAlias, $extraParams);

        return $this->buildTree($results);
    }

    public function getTreeExceptNodeAndItsChildrenQB(TreeNodeInterface $treeNode, string $rootAlias = 't')
    {
        return $this->getFlatTreeQB('', $rootAlias)
            ->andWhere($rootAlias . '.materializedPath NOT LIKE :except_path')
            ->andWhere($rootAlias . '.id != :id')
            ->setParameter('except_path', $treeNode->getRealMaterializedPath() . '%')
            ->setParameter('id', $treeNode->getId());
    }

    /**
     * Extracts the root node and constructs a tree using flat resultset
     * @return ArrayAccess|TreeNodeInterface[]|null
     */
    public function buildTree(array $results)
    {
        if (count($results) === 0) {
            return null;
        }

        $root = $results[0];
        $root->buildTree($results);

        return $root;
    }

    /**
     * Constructs a query builder to get a flat tree, starting from a given path
     */
    public function getFlatTreeQB(string $path = '', string $rootAlias = 't', array $extraParams = []): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder($rootAlias)
            ->andWhere($rootAlias . '.materializedPath LIKE :path')
            ->addOrderBy($rootAlias . '.materializedPath', 'ASC')
            ->setParameter('path', $path . '%');

        $parentId = basename($path);
        if ($parentId) {
            $queryBuilder->orWhere($rootAlias . '.id = :parent')
                ->setParameter('parent', $parentId);
        }

        $this->addFlatTreeConditions($queryBuilder, $extraParams);

        return $queryBuilder;
    }

    public function getFlatTree(string $path, string $rootAlias = 't', array $extraParams = []): array
    {
        return $this->getFlatTreeQB($path, $rootAlias, $extraParams)
            ->getQuery()
            ->execute();
    }

    /**
     * Manipulates the flat tree query builder before executing it.
     * Override this method to customize the tree query
     */
    protected function addFlatTreeConditions(QueryBuilder $queryBuilder, array $extraParams): void
    {
    }
}
