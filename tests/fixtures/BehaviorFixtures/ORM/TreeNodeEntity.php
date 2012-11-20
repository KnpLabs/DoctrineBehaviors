<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Knp\DoctrineBehaviors\Model\Tree;

/**
 * @ORM\Entity(repositoryClass="BehaviorFixtures\ORM\TreeNodeEntityRepository")
 * @ODM\Document
 * @ODM\EmbeddedDocument
 */
class TreeNodeEntity implements Tree\NodeInterface, \ArrayAccess
{
    const PATH_SEPARATOR = '/';

    use Tree\Node;

    /**
     * @ODM\Id(strategy="NONE")
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    public function __construct($id = null)
    {
        $this->id = $id;
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

