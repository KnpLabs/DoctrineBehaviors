<?php

namespace Knp\DoctrineBehaviors\ORM\Tree;

use Knp\DoctrineBehaviors\ORM\Tree\NodeInterface;

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
    private $children;

    /**
     * @param NodeInterface the parent in the tree
     */
    private $parent;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path = '';

    /**
     * {@inheritdoc}
     **/
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     **/
    public function setPath($path)
    {
        $this->path = $path;
        $this->setParentPath($this->getParentPath());

        return $this;
    }

    /**
     * {@inheritdoc}
     **/
    public function getNodeChildren()
    {
        return $this->children = $this->children ?: new ArrayCollection;
    }

    public function isLeaf()
    {
        return 0 === $this->getNodeChildren()->count();
    }

    public function isRoot()
    {
        return self::getPathSeparator() === $this->getParentPath();
    }

    /**
     * {@inheritdoc}
     **/
    public function addChild(NodeInterface $node)
    {
        $this->getNodeChildren()->add($node);
    }

    /**
     * {@inheritdoc}
     **/
    public function isChildOf(NodeInterface $node)
    {
        return $this->getParentPath() === $node->getPath();
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

        $path = rtrim($node->getPath(), static::getPathSeparator());
        $this->setPath($path . static::getPathSeparator() . $this->getId());

        if (null !== $this->parent) {
            $this->parent->getNodeChildren()->removeElement($this);
        }

        $this->parent = $node;
        $this->parent->addChild($this);

        foreach($this->getNodeChildren() as $child)
        {
            $child->setChildOf($this);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     **/
    public function getParentPath()
    {
        $path = $this->getExplodedPath();
        array_pop($path);

        $parent_path = static::getPathSeparator().implode(static::getPathSeparator(), $path);

        return $parent_path;
    }

    /**
     * {@inheritdoc}
     **/
    public function setParentPath($path)
    {
        $this->parent_path = $path;
    }

    /**
     * {@inheritdoc}
     **/
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     **/
    public function setParent(NodeInterface $node)
    {
        $this->parent = $node;
        $this->setChildOf($this->parent);

        return $this;
    }

    /**
     * {@inheritdoc}
     **/
    public function getExplodedPath()
    {
        $path = explode(static::getPathSeparator(), $this->getPath());

        return array_filter($path, function($item) {
            return '' !== $item;
        });
    }

    /**
     * {@inheritdoc}
     **/
    public function getLevel()
    {
        return count($this->getExplodedPath());
    }

    /**
     * {@inheritdoc}
     **/
    public function getRootPath()
    {
        $explodedPath = $this->getExplodedPath();

        return static::getPathSeparator() . array_shift($explodedPath);
    }

    /**
     * {@inheritdoc}
     **/
    public function getRoot()
    {
        $parent = $this;
        while(null !== $parent->getParent()) {
            $parent = $parent->getParent();
        }

        return $parent;
    }

    /**
     * {@inheritdoc}
     **/
    public function buildTree(array $results)
    {
        $tree = array($this->getPath() => $this);
        foreach($results as $node) {

            $tree[$node->getPath()] = $node;

            $parent = isset($tree[$node->getParentPath()]) ? $tree[$node->getParentPath()] : $this; // root is the fallback parent
            if ($parent !== $node) {
                $parent->addChild($node);
                $node->setParent($parent);
            }
        }
    }

    /**
     * Returns path separator for entity's materialized path.
     *
     * @return string "/" by default
     */
    static public function getPathSeparator()
    {
        return '/';
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

        foreach($this->getNodeChildren() as $node) {
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
                $pre = $node->getLevel() > 1 ? implode('', array_fill(0, $node->getLevel(), '--')) : '';
                return $pre.(string)$node;
            };
        }
        if (null === $tree) {
            $tree = array($this->getId() => $prepare($this));
        }

        foreach($this->getNodeChildren() as $node) {
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
        return isset($this->getNodeChildren()[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->getNodeChildren()[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->getNodeChildren()[$offset];
    }
}
