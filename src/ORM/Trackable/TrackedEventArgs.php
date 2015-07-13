<?php

namespace Knp\DoctrineBehaviors\ORM\Trackable;

use Doctrine\Common\Collections\Collection,
    Doctrine\Common\Collections\ArrayCollection;

class TrackedEventArgs
{
   protected $eventArgs;

   protected $metadata;

   public function __construct(EventArgs $eventArgs, Collection $metadata = null)
   {
      $this->eventArgs = $eventArgs;
      $this->metadata  = $metadata ?: new ArrayCollection;
   }

   /**
    * Returns the Collection of Metadata.
    * This collection can be used as a Read/Write access.
    */
   public function & getMetadata()
   {
      return $this->metadata;
   }

   public function __call($name, $arguments)
   {
      return call_user_func_array([$this->eventArgs, $name], $arguments);
   }
}
