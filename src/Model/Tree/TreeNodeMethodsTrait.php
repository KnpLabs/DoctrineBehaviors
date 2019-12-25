<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Tree;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use LogicException;
use Nette\Utils\Json;

trait TreeNodeMethodsTrait
{
    /**
     * @return string|int|null
     */
    public function getNodeId()
    {
        return $this->getId();
    }

    public static function getMaterializedPathSeparator(): string
    {
        return '/';
    }

    public function getRealMaterializedPath(): string
    {
        return $this->getMaterializedPath() . self::getMaterializedPathSeparator() . $this->getNodeId();
    }

    public function getMaterializedPath(): string
    {
        return $this->materializedPath;
    }

    public function setMaterializedPath(string $path): void
    {
        $this->materializedPath = $path;
        $this->setParentMaterializedPath($this->getParentMaterializedPath());
    }

    public function getParentMaterializedPath(): string
    {
        $path = $this->getExplodedPath();
        array_pop($path);

        return static::getMaterializedPathSeparator() . implode(static::getMaterializedPathSeparator(), $path);
    }

    public function setParentMaterializedPath($path): void
    {
        $this->parentNodePath = $path;
    }

    public function getRootMaterializedPath(): string
    {
        $explodedPath = $this->getExplodedPath();

        return static::getMaterializedPathSeparator() . array_shift($explodedPath);
    }

    public function getNodeLevel(): int
    {
        return count($this->getExplodedPath());
    }

    public function isRootNode(): bool
    {
        return self::getMaterializedPathSeparator() === $this->getParentMaterializedPath();
    }

    public function isLeafNode(): bool
    {
        return $this->getChildNodes()->count() === 0;
    }

    /**
     * @return Collection|TreeNodeInterface[]
     */
    public function getChildNodes(): Collection
    {
        // set default value as in entity constructors
        if ($this->childNodes === null) {
            $this->childNodes = new ArrayCollection();
        }

        return $this->childNodes;
    }

    public function addChildNode(TreeNodeInterface $treeNode): void
    {
        $this->getChildNodes()->add($treeNode);
    }

    public function isIndirectChildNodeOf(TreeNodeInterface $treeNode): bool
    {
        return $this->getRealMaterializedPath() !== $treeNode->getRealMaterializedPath()
            && strpos($this->getRealMaterializedPath(), $treeNode->getRealMaterializedPath()) === 0;
    }

    public function isChildNodeOf(TreeNodeInterface $treeNode): bool
    {
        return $this->getParentMaterializedPath() === $treeNode->getRealMaterializedPath();
    }

    public function setChildNodeOf(?TreeNodeInterface $treeNode = null): void
    {
        $id = $this->getNodeId();
        if (empty($id)) {
            throw new LogicException('You must provide an id for this node if you want it to be part of a tree.');
        }

        $path = $treeNode !== null
            ? rtrim($treeNode->getRealMaterializedPath(), static::getMaterializedPathSeparator())
            : static::getMaterializedPathSeparator();
        $this->setMaterializedPath($path);

        if ($this->parentNode !== null) {
            $this->parentNode->getChildNodes()->removeElement($this);
        }

        $this->parentNode = $treeNode;

        if ($treeNode !== null) {
            $this->parentNode->addChildNode($this);
        }

        foreach ($this->getChildNodes() as $child) {
            /** @var TreeNodeInterface $this */
            $child->setChildNodeOf($this);
        }
    }

    public function getParentNode(): ?TreeNodeInterface
    {
        return $this->parentNode;
    }

    public function setParentNode(TreeNodeInterface $treeNode): void
    {
        $this->parentNode = $treeNode;
        $this->setChildNodeOf($this->parentNode);
    }

    public function getRootNode(): TreeNodeInterface
    {
        $parent = $this;
        while ($parent->getParentNode() !== null) {
            $parent = $parent->getParentNode();
        }

        return $parent;
    }

    public function buildTree(array $results): void
    {
        $this->getChildNodes()->clear();
        foreach ($results as $node) {
            if ($node->getMaterializedPath() === $this->getRealMaterializedPath()) {
                $node->setParentNode($this);
                $node->buildTree($results);
            }
        }
    }

    /**
     * @param Closure $prepare a function to prepare the node before putting into the result
     */
    public function toJson(?Closure $prepare = null): string
    {
        $tree = $this->toArray($prepare);

        return Json::encode($tree);
    }

    /**
     * @param Closure $prepare a function to prepare the node before putting into the result
     */
    public function toArray(?Closure $prepare = null, ?array &$tree = null): array
    {
        if ($prepare === null) {
            $prepare = function (TreeNodeInterface $node) {
                return (string) $node;
            };
        }
        if ($tree === null) {
            $tree = [
                $this->getNodeId() => [
                    /** @var TreeNodeInterface $this */
                    'node' => $prepare($this),
                    'children' => [],
                ],
            ];
        }

        foreach ($this->getChildNodes() as $node) {
            $tree[$this->getNodeId()]['children'][$node->getNodeId()] = [
                'node' => $prepare($node),
                'children' => [],
            ];

            $node->toArray($prepare, $tree[$this->getNodeId()]['children']);
        }

        return $tree;
    }

    /**
     * @param Closure $prepare a function to prepare the node before putting into the result
     * @param array    $tree    a reference to an array, used internally for recursion
     */
    public function toFlatArray(?Closure $prepare = null, ?array &$tree = null): array
    {
        if ($prepare === null) {
            $prepare = function (TreeNodeInterface $node) {
                $pre = $node->getNodeLevel() > 1 ? implode('', array_fill(0, $node->getNodeLevel(), '--')) : '';

                return $pre . $node;
            };
        }

        if ($tree === null) {
            $tree = [$this->getNodeId() => $prepare($this)];
        }

        foreach ($this->getChildNodes() as $node) {
            $tree[$node->getNodeId()] = $prepare($node);
            $node->toFlatArray($prepare, $tree);
        }

        return $tree;
    }

    /**
     * @param TreeNodeInterface $node
     */
    public function offsetSet($offset, $node): void
    {
        /** @var TreeNodeInterface $this */
        $node->setChildNodeOf($this);
    }

    public function offsetExists($offset)
    {
        return isset($this->getChildNodes()[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->getChildNodes()[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->getChildNodes()[$offset];
    }

    /**
     * @return string[]
     */
    protected function getExplodedPath(): array
    {
        $path = explode(static::getMaterializedPathSeparator(), $this->getRealMaterializedPath());

        return array_filter($path, function ($item) {
            return $item !== '';
        });
    }
}
