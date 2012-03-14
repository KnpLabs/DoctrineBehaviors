<?php


namespace Knp\DoctrineBehaviors\ORM\Tree;

use Doctrine\Common\Collections\Collection;

/**
 * Tree\Node defines a set of needed methods
 * to work with materialized path tree nodes
 *
 * @author     Florian Klein <florian.klein@free.fr>
 */
interface LeafInterface
{
    /**
     * @return string the id that will represent the node in the path
     **/
    function getId();

    /**
     * @return string the materialized path,
     * eg the representation of path from all ancestors
     **/
    function getPath();

    /**
     * @return string the materialized path from the parent, eg: the representation of path from all parent ancestors
     **/
    function getParentPath();

    /**
     * Set parent path.
     *
     * @param path the value to set.
     */
    function setParentPath($path);


    /**
     * @return LeafInterface the parent node
     **/
    function getParent();

    /**
     * @param string $path the materialized path, eg: the the materialized path to its parent
     *
     * @return LeafInterface $this Fluent interface
     **/
    function setPath($path);

    /**
     * Used to build the hierarchical tree.
     * This method will do:
     *    - modify the parent of this node
     *    - Add the this node to the children of the new parent
     *    - Remove the this node from the children of the old parent
     *    - Modify the materialized path of this node and all its children, recursively
     *
     * @param LeafInterface $node The node to use as a parent
     *
     * @return LeafInterface $this Fluent interface
     **/
    function setChildOf(LeafInterface $node);

    /**
     * @param LeafInterface the node to append to the children collection
     *
     * @return LeafInterface $this Fluent interface
     **/
    function addChild(LeafInterface $node);

    /**
     * @return Collection the children collection
     **/
    function getNodeChildren();

    /**
     * Tells if this node is a child of another node
     * @param LeafInterface $node the node to compare with
     *
     * @return boolean true if this node is a direct child of $node
     **/
    function isChildOf(LeafInterface $node);

    /**
     *
     * @return integer the level of this node, eg: the depth compared to root node
     **/
    function getLevel();

    /**
     * Builds a hierarchical tree from a flat collection of LeafInterface elements
     *
     * @return void
     **/
    function buildTree(\Traversable $nodes);
}

