<?php

declare(strict_types=1);

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\Tree;

use Doctrine\Common\Collections\ArrayCollection;

/*
 * @author     Florian Klein <florian.klein@free.fr>
 */
trait Node
{
    protected $materializedPath = '';

    /**
     * @var ArrayCollection the children in the tree
     */
    private $childNodes;

    /**
     * @var NodeInterface the parent in the tree
     */
    private $parentNode;

    public function getNodeId()
    {
        return $this->getId();
    }

    /**
     * Returns path separator for entity's materialized path.
     *
     * @return string "/" by default
     */
    public static function getMaterializedPathSeparator()
    {
        return '/';
    }

    public function getRealMaterializedPath()
    {
        return $this->getMaterializedPath() . self::getMaterializedPathSeparator() . $this->getNodeId();
    }

    public function getMaterializedPath()
    {
        return $this->materializedPath;
    }

    public function setMaterializedPath($path)
    {
        $this->materializedPath = $path;
        $this->setParentMaterializedPath($this->getParentMaterializedPath());

        return $this;
    }

    public function getParentMaterializedPath()
    {
        $path = $this->getExplodedPath();
        array_pop($path);

        return static::getMaterializedPathSeparator() . implode(static::getMaterializedPathSeparator(), $path);
    }

    public function setParentMaterializedPath($path): void
    {
        $this->parentNodePath = $path;
    }

    public function getRootMaterializedPath()
    {
        $explodedPath = $this->getExplodedPath();

        return static::getMaterializedPathSeparator() . array_shift($explodedPath);
    }

    public function getNodeLevel()
    {
        return count($this->getExplodedPath());
    }

    public function isRootNode()
    {
        return self::getMaterializedPathSeparator() === $this->getParentMaterializedPath();
    }

    public function isLeafNode()
    {
        return $this->getChildNodes()->count() === 0;
    }

    public function getChildNodes()
    {
        return $this->childNodes = $this->childNodes ?: new ArrayCollection();
    }

    public function addChildNode(NodeInterface $node): void
    {
        $this->getChildNodes()->add($node);
    }

    public function isIndirectChildNodeOf(NodeInterface $node)
    {
        return $this->getRealMaterializedPath() !== $node->getRealMaterializedPath()
            && strpos($this->getRealMaterializedPath(), $node->getRealMaterializedPath()) === 0;
    }

    public function isChildNodeOf(NodeInterface $node)
    {
        return $this->getParentMaterializedPath() === $node->getRealMaterializedPath();
    }

    public function setChildNodeOf(?NodeInterface $node = null)
    {
        $id = $this->getNodeId();
        if (empty($id)) {
            throw new \LogicException('You must provide an id for this node if you want it to be part of a tree.');
        }

        $path = $node !== null
            ? rtrim($node->getRealMaterializedPath(), static::getMaterializedPathSeparator())
            : static::getMaterializedPathSeparator()
        ;
        $this->setMaterializedPath($path);

        if ($this->parentNode !== null) {
            $this->parentNode->getChildNodes()->removeElement($this);
        }

        $this->parentNode = $node;

        if ($node !== null) {
            $this->parentNode->addChildNode($this);
        }

        foreach ($this->getChildNodes() as $child) {
            $child->setChildNodeOf($this);
        }

        return $this;
    }

    public function getParentNode()
    {
        return $this->parentNode;
    }

    public function setParentNode(NodeInterface $node)
    {
        $this->parentNode = $node;
        $this->setChildNodeOf($this->parentNode);

        return $this;
    }

    public function getRootNode()
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
     * @param \Closure $prepare a function to prepare the node before putting into the result
     *
     * @return string the json representation of the hierarchical result
     **/
    public function toJson(?\Closure $prepare = null)
    {
        $tree = $this->toArray($prepare);

        return json_encode($tree);
    }

    /**
     * @param \Closure $prepare a function to prepare the node before putting into the result
     * @param array    $tree    a reference to an array, used internally for recursion
     *
     * @return array the hierarchical result
     **/
    public function toArray(?\Closure $prepare = null, ?array &$tree = null)
    {
        if ($prepare === null) {
            $prepare = function (NodeInterface $node) {
                return (string) $node;
            };
        }
        if ($tree === null) {
            $tree = [$this->getNodeId() => ['node' => $prepare($this), 'children' => []]];
        }

        foreach ($this->getChildNodes() as $node) {
            $tree[$this->getNodeId()]['children'][$node->getNodeId()] = ['node' => $prepare($node), 'children' => []];
            $node->toArray($prepare, $tree[$this->getNodeId()]['children']);
        }

        return $tree;
    }

    /**
     * @param \Closure $prepare a function to prepare the node before putting into the result
     * @param array    $tree    a reference to an array, used internally for recursion
     *
     * @return array the flatten result
     **/
    public function toFlatArray(?\Closure $prepare = null, ?array &$tree = null)
    {
        if ($prepare === null) {
            $prepare = function (NodeInterface $node) {
                $pre = $node->getNodeLevel() > 1 ? implode('', array_fill(0, $node->getNodeLevel(), '--')) : '';

                return $pre . (string) $node;
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

    public function offsetSet($offset, $node)
    {
        $node->setChildNodeOf($this);

        return $this;
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

    protected function getExplodedPath()
    {
        $path = explode(static::getMaterializedPathSeparator(), $this->getRealMaterializedPath());

        return array_filter($path, function ($item) {
            return $item !== '';
        });
    }
}
