<?php

namespace Knp\Component\Test\MaterializedPath\Fixture;


use Knp\Component\Tree\MaterializedPath\Node;
use Knp\Component\Tree\MaterializedPath\NodeInterface;

use Doctrine\Common\Collections\ArrayCollection;


class MenuItem implements NodeInterface, \ArrayAccess
{
    const PATH_SEPARATOR = '/';

    use Node {

    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @param Collection the children in the tree
     */
    private $children;

    /**
     * @param NodeInterface the parent in the tree
     */
    private $parent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sort;

    public function __construct()
    {
        $this->children = new ArrayCollection;
    }

    public function __toString()
    {
        return (string) $this->name;
    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  string
     * @return null
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string
     * @return null
     */
    public function setName($name)
    {
        $this->name = $name;
    }

}

