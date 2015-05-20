<?php

namespace BehaviorFixtures\ORM;

use Knp\DoctrineBehaviors\Model;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class ActivatableEntity
 * @package BehaviorFixtures\ORM
 * @ORM\Entity
 */
class ActivatableEntity
{
    use Model\State\Activatable;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    /**
     * Returns object id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get title.
     *
     * @return title.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param title the value to set.
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}