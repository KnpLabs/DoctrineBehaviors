<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model;
use Knp\DoctrineBehaviors\ORM\Trackable\TrackedEventArgs;

/**
 * @ORM\Entity
 */
class TrackableEntity
{
    use Model\Trackable\Trackable {
        Model\Trackable\Trackable::trackCreation as private parentTrackCreation;
        Model\Trackable\Trackable::trackChange   as private parentTrackChange;
        Model\Trackable\Trackable::trackDeletion as private parentTrackDeletion;
    }

    public function trackCreation(TrackedEventArgs $eventArgs)
    {
        $this->trackedEvent     = 'creation';
        $this->trackedEventArgs = $eventArgs;

        $this->parentTrackCreation($eventArgs);
    }
    
    public function trackChange(TrackedEventArgs $eventArgs)
    {
        $this->trackedEvent     = 'change';
        $this->trackedEventArgs = $eventArgs;

        $this->parentTrackChange($eventArgs);
    }
    
    public function trackDeletion(TrackedEventArgs $eventArgs)
    {
        $this->trackedEvent     = 'deletion';
        $this->trackedEventArgs = $eventArgs;

        $this->parentTrackDeletion($eventArgs);
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

    public $trackedEvent;

    public $trackedEventArgs;

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
