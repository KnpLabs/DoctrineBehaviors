<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

use Doctrine\Common\Collections\Collection;

/**
 * Tree\Node defines a set of needed methods to work with materialized path tree nodes
 */
interface TreeNodeInterface
{
    public function __toString(): string;

    /**
     * @return string|int|null The field that will represent the node in the path
     */
    public function getNodeId();

    /**
     * @return string The representation of path from all ancestors
     */
    public function getMaterializedPath(): string;

    /**
     * @return string The representation of path from all ancestors + current node
     */
    public function getRealMaterializedPath(): string;

    /**
     * @return string The representation of path from all parent ancestors
     */
    public function getParentMaterializedPath(): string;

    public function setParentMaterializedPath(string $path): void;

    public function getParentNode(): ?self;

    public function setParentNode(self $treeNode): void;

    /**
     * @param string $path the materialized path, eg: the the materialized path to its parent
     */
    public function setMaterializedPath(string $path): void;

    /**
     * Used to build the hierarchical tree.
     * This method will:
     *    - modify the parent of this node
     *    - Add this node to the children of the new parent
     *    - Remove this node from the children of the old parent
     *    - Modify the materialized path of this node and all its children, recursively
     */
    public function setChildNodeOf(?self $treeNode = null): void;

    public function addChildNode(self $treeNode): void;

    /**
     * @return Collection<TreeNodeInterface>
     */
    public function getChildNodes(): Collection;

    public function isLeafNode(): bool;

    public function isRootNode(): bool;

    public function getRootNode(): self;

    public function isChildNodeOf(self $treeNode): bool;

    public function getNodeLevel(): int;

    /**
     * Builds a hierarchical tree from a flat collection of NodeInterface elements
     *
     * @param self[] $treeNodes
     */
    public function buildTree(array $treeNodes): void;
}
