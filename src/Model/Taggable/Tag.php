<?php

namespace Knp\DoctrineBehaviors\Model\Taggable;

use Doctrine\ORM\Mapping as ORM;

trait Tag
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $nameCanonical;

    /**
     * @var \Knp\DoctrineBehaviors\Model\Taggable\TaggableInterface
     */
    protected $taggable;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getNameCanonical()
    {
        return $this->nameCanonical;
    }

    /**
     * @param string $nameCanonical
     */
    public function setNameCanonical($nameCanonical)
    {
        $this->nameCanonical = $nameCanonical;
    }

    /**
     * @return \Knp\DoctrineBehaviors\Model\Taggable\TaggableInterface
     */
    public function getTaggable()
    {
        return $this->taggable;
    }

    /**
     * @param \Knp\DoctrineBehaviors\Model\Taggable\TaggableInterface $taggable
     */
    public function setTaggable(TaggableInterface $taggable)
    {
        $this->taggable = $taggable;
    }
}
