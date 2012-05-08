<?php

namespace Knp\DoctrineBehaviors\Model\Tree;

use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/*
 * @author     Florian Klein <florian.klein@free.fr>
 */
trait Node
{
    /**
     * @param Collection the children in the tree
     */
    private $childNodes;

    /**
     * @param NodeInterface the parent in the tree
     */
    private $parentNode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $materializedPath = '';

    /**
     * Returns path separator for entity's materialized path.
     *
     * @return string "/" by default
     */
    static public function getMaterializedPathSeparator()
    {
        return '/';
    }

    /**
     * {@inheritdoc}
     **/
    public function getRealMaterializedPath()
    {
        return $this->getMaterializedPath() . self::getMaterializedPathSeparator() . $this->getId();
    }

    public function getMaterializedPath()
    {
        return $this->materializedPath;
    }

    /**
     * {@inheritdoc}
     **/
    public function setMaterializedPath($path)
    {
        $this->materializedPath = $path;
        $this->setParentMaterializedPath($this->getParentMaterializedPath());

        return $this;
    }

    /**
     * {@inheritdoc}
     **/
    public function getParentMaterializedPath()
    {
        $path = $this->getExplodedPath();
        array_pop($path);

        $parentPath = static::getMaterializedPathSeparator().implode(static::getMaterializedPathSeparator(), $path);

        return $parentPath;
    }

    /**
     * {@inheritdoc}
     **/
    public function setParentMaterializedPath($path)
    {
        $this->parentNodePath = $path;
    }

    /**
     * {@inheritdoc}
     **/
    public function getRootMaterializedPath()
    {
        $explodedPath = $this->getExplodedPath();

        return static::getMaterializedPathSeparator() . array_shift($explodedPath);
    }

    /**
     * {@inheritdoc}
     **/
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
        return 0 === $this->getChildren()->count();
    }

    /**
     * {@inheritdoc}
     **/
    public function getChildren()
    {
        return $this->childNodes = $this->childNodes ?: new ArrayCollection;
    }

    /**
     * {@inheritdoc}
     **/
    public function addChild(NodeInterface $node)
    {
        $this->getChildren()->add($node);
    }

    /**
     * {@inheritdoc}
     **/
    public function isIndirectChildOf(NodeInterface $node)
    {
        return $this->getRealMaterializedPath() !== $node->getRealMaterializedPath()
            && 0 === strpos($this->getRealMaterializedPath(), $node->getRealMaterializedPath());
    }

    /**
     * {@inheritdoc}
     **/
    public function isChildOf(NodeInterface $node)
    {
        return $this->getParentMaterializedPath() === $node->getRealMaterializedPath();
    }

    /**
     * {@inheritdoc}
     **/
    public function setChildOf(NodeInterface $node)
    {
        $id = $this->getId();
        if (empty($id)) {
            throw new \LogicException('You must provide an id for this node if you want it to be part of a tree.');
        }

        $path = rtrim($node->getRealMaterializedPath(), static::getMaterializedPathSeparator());
        $this->setMaterializedPath($path);

        if (null !== $this->parentNode) {
            $this->parentNode->getChildren()->removeElement($this);
        }

        $this->parentNode = $node;
        $this->parentNode->addChild($this);

        foreach($this->getChildren() as $child)
        {
            $child->setChildOf($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     **/
    public function getParentNode()
    {
        return $this->parentNode;
    }

    /**
     * {@inheritdoc}
     **/
    public function setParentNode(NodeInterface $node)
    {
        $this->parentNode = $node;
        $this->setChildOf($this->parentNode);

        return $this;
    }

    /**
     * {@inheritdoc}
     **/
    public function getRootNode()
    {
        $parent = $this;
        while(null !== $parent->getParentNode()) {
            $parent = $parent->getParentNode();
        }

        return $parent;
    }

    /**
     * {@inheritdoc}
     **/
    public function buildTree(array $results)
    {
        $this->getChildren()->clear();
        foreach ($results as $i => $node) {
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
    public function toJson(\Closure $prepare = null)
    {
        $tree = $this->toArray($prepare);

        return json_encode($tree);
    }

    /**
     * @param \Closure $prepare a function to prepare the node before putting into the result
     * @param array $tree a reference to an array, used internally for recursion
     *
     * @return array the hierarchical result
     **/
    public function toArray(\Closure $prepare = null, array &$tree = null)
    {
        if(null === $prepare) {
            $prepare = function(NodeInterface $node) {
                return (string)$node;
            };
        }
        if (null === $tree) {
            $tree = array($this->getId() => array('node' => $prepare($this), 'children' => array()));
        }

        foreach($this->getChildren() as $node) {
            $tree[$this->getId()]['children'][$node->getId()] = array('node' => $prepare($node), 'children' => array());
            $node->toArray($prepare, $tree[$this->getId()]['children']);
        }

        return $tree;
    }

    /**
     * @param \Closure $prepare a function to prepare the node before putting into the result
     * @param array $tree a reference to an array, used internally for recursion
     *
     * @return array the flatten result
     **/
    public function toFlatArray(\Closure $prepare = null, array &$tree = null)
    {
        if(null === $prepare) {
            $prepare = function(NodeInterface $node) {
                $pre = $node->getNodeLevel() > 1 ? implode('', array_fill(0, $node->getNodeLevel(), '--')) : '';
                return $pre.(string)$node;
            };
        }
        if (null === $tree) {
            $tree = array($this->getId() => $prepare($this));
        }

        foreach($this->getChildren() as $node) {
            $tree[$node->getId()] = $prepare($node);
            $node->toFlatArray($prepare, $tree);
        }

        return $tree;
    }

    public function offsetSet($offset, $node)
    {
        $node->setChildOf($this);

        return $this;
    }

    public function offsetExists($offset)
    {
        return isset($this->getChildren()[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->getChildren()[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->getChildren()[$offset];
    }

    /**
     * {@inheritdoc}
     **/
    protected function getExplodedPath()
    {
        $path = explode(static::getMaterializedPathSeparator(), $this->getRealMaterializedPath());

        return array_filter($path, function($item) {
            return '' !== $item;
        });
    }
}
