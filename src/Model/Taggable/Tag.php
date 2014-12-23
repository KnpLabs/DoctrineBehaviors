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
     * @var \Knp\DoctrineBehaviors\Model\Taggable\Taggable
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
}
