<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;

/**
 * @ORM\Entity
 */
class RenamedBlameableEntity
{
    use Blameable
    {
         Blameable::setCreatedBy as setTraitCreatedBy;
         Blameable::setUpdatedBy as setTraitUpdatedBy;
         Blameable::setDeletedBy as setTraitDeletedBy;
         Blameable::getCreatedBy as getTraitCreatedBy;
         Blameable::getUpdatedBy as getTraitUpdatedBy;
         Blameable::getDeletedBy as getTraitDeletedBy;
         Blameable::isBlameable  as isTraitBlameable;
    }

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

    public function setCreatedBy()
    {
        throw new BadMethodCallException($this, 'setCreatedBy');
    }

    public function setUpdatedBy()
    {
        throw new BadMethodCallException($this, 'setUpdatedBy');
    }

    public function setDeletedBy()
    {
        throw new BadMethodCallException($this, 'setDeletedBy');
    }

    public function getCreatedBy()
    {
        throw new BadMethodCallException($this, 'getCreatedBy');
    }

    public function getUpdatedBy()
    {
        throw new BadMethodCallException($this, 'getUpdatedBy');
    }

    public function getDeletedBy()
    {
        throw new BadMethodCallException($this, 'getDeletedBy');
    }

    public function isBlameable()
    {
        throw new BadMethodCallException($this, 'isBlameable');
    }
}
