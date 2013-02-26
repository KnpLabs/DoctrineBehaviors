<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;

/**
 * @ORM\Entity
 */
class RenamedLoggableEntity
{
    use Model\Loggable\Loggable
    {
        Model\Loggable\Loggable::getUpdateLogMessage as getTraitUpdateLogMessage;
        Model\Loggable\Loggable::getRemoveLogMessage as getTraitRemoveLogMessage;
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

    public function getUpdateLogMessage()
    {
        throw new BadMethodCallException($this, 'getUpdateLogMessage');
    }

    public function getRemoveLogMessage()
    {
        throw new BadMethodCallException($this, 'getRemoveLogMessage');
    }
}
