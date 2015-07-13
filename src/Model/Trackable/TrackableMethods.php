<?php

namespace Knp\DoctrineBehaviors\Model\Trackable;

trait TrackableMethods
{
    public function trackCreation(TrackedEventArgs $eventArgs)
    {
       if (method_exists($this, 'trackBlameableCreation')
          && $eventArgs->getMetadata()->containsKey('user')) {
          $this->trackBlameableCreation($eventArgs);
        }

       if (method_exists($this, 'trackTimestampableCreation')
          && $eventArgs->getMetadata()->containsKey('timestamp')) {
          $this->trackTimestampableCreation($eventArgs);
       }
    }

    public function trackChange(TrackedEventArgs $eventArgs)
    {
       if (method_exists($this, 'trackBlameableChange')
          && $eventArgs->getMetadata()->containsKey('user')) {
          $this->trackBlameableChange($eventArgs);
        }

       if (method_exists($this, 'trackTimestampableChange')
          && $eventArgs->getMetadata()->containsKey('timestamp')) {
          $this->trackTimestampableChange($eventArgs);
       }
    }

    public function trackDeletion(TrackedEventArgs $eventArgs)
    {
       if (method_exists($this, 'trackBlameableDeletion')
          && $eventArgs->getMetadata()->containsKey('user')) {
          $this->trackBlameableDeletion($eventArgs);
        }

       if (method_exists($this, 'trackTimestampableDeletion')
          && $eventArgs->getMetadata()->containsKey('timestamp')) {
          $this->trackTimestampableDeletion($eventArgs);
       }
    }
}
