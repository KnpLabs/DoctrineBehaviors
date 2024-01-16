<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Tree;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;
use Knp\DoctrineBehaviors\Exception\TreeException;
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
        if ($this->getMaterializedPath() === self::getMaterializedPathSeparator()) {
            return $this->getMaterializedPath() . $this->getNodeId();
        }

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

    public function setParentMaterializedPath(string $path): void
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
        return $this->getChildNodes()
            ->count() === 0;
    }

    /**
     * @return Collection<TreeNodeInterface>
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
        $this->getChildNodes()
            ->add($treeNode);
    }

    public function isIndirectChildNodeOf(TreeNodeInterface $treeNode): bool
    {
        return $this->getRealMaterializedPath() !== $treeNode->getRealMaterializedPath()
            && str_starts_with($this->getRealMaterializedPath(), $treeNode->getRealMaterializedPath());
    }

    public function isChildNodeOf(TreeNodeInterface $treeNode): bool
    {
        return $this->getParentMaterializedPath() === $treeNode->getRealMaterializedPath();
    }

    public function setChildNodeOf(?TreeNodeInterface $treeNode = null): void
    {
        $id = $this->getNodeId();
        if ($id === '' || $id === null) {
            throw new TreeException('You must provide an id for this node if you want it to be part of a tree.');
        }

        $path = $treeNode !== null
            ? rtrim($treeNode->getRealMaterializedPath(), static::getMaterializedPathSeparator())
            : static::getMaterializedPathSeparator();
        $this->setMaterializedPath($path);

        if ($this->parentNode !== null) {
            $this->parentNode->getChildNodes()
                ->removeElement($this);
        }

        $this->parentNode = $treeNode;

        if ($treeNode !== null) {
            $this->parentNode->addChildNode($this);
        }

        foreach ($this->getChildNodes() as $childNode) {
            /** @var TreeNodeInterface $this */
            $childNode->setChildNodeOf($this);
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

    /**
     * @param TreeNodeInterface[] $treeNodes
     */
    public function buildTree(array $treeNodes): void
    {
        $this->getChildNodes()
            ->clear();

        foreach ($treeNodes as $treeNode) {
            if ($treeNode->getMaterializedPath() !== $this->getRealMaterializedPath()) {
                continue;
            }

            $treeNode->setParentNode($this);
            $treeNode->buildTree($treeNodes);
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
            $prepare = static fn (TreeNodeInterface $node): string => (string) $node;
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

        foreach ($this->getChildNodes() as $childNode) {
            $tree[$this->getNodeId()]['children'][$childNode->getNodeId()] = [
                'node' => $prepare($childNode),
                'children' => [],
            ];

            $childNode->toArray($prepare, $tree[$this->getNodeId()]['children']);
        }

        return $tree;
    }

    /**
     * @param Closure $prepare a function to prepare the node before putting into the result
     * @param array $tree a reference to an array, used internally for recursion
     */
    public function toFlatArray(?Closure $prepare = null, ?array &$tree = null): array
    {
        if ($prepare === null) {
            $prepare = static function (TreeNodeInterface $treeNode) {
                $pre = $treeNode->getNodeLevel() > 1 ? implode('', array_fill(0, $treeNode->getNodeLevel(), '--')) : '';
                return $pre . $treeNode;
            };
        }

        if ($tree === null) {
            $tree = [
                $this->getNodeId() => $prepare($this),
            ];
        }

        foreach ($this->getChildNodes() as $childNode) {
            $tree[$childNode->getNodeId()] = $prepare($childNode);
            $childNode->toFlatArray($prepare, $tree);
        }

        return $tree;
    }

    /**
     * @param TreeNodeInterface $node
     */
    public function offsetSet(mixed $offset, $node): void
    {
        /** @var TreeNodeInterface $this */
        $node->setChildNodeOf($this);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->getChildNodes()[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->getChildNodes()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getChildNodes()[$offset];
    }

    /**
     * @return string[]
     */
    protected function getExplodedPath(): array
    {
        $separator = static::getMaterializedPathSeparator();
        if ($separator === '') {
            throw new ShouldNotHappenException();
        }

        $path = explode($separator, $this->getRealMaterializedPath());

        return array_filter($path, static fn ($item): bool => $item !== '');
    }
}
