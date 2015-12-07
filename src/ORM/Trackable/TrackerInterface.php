<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Trackable;

use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * TrackerInterface can be used to create services providing meta-data for
 * Trackable entities
 */
interface TrackerInterface
{
   /**
    * Tracker's name
    *
    * @return string
    */
   public function getName();

   /**
    * Choose whether the current tracker should be used for the current Event
    *
    * @param $eventArgs LifecycleEventArgs
    *
    * @return boolean
    */
   public function isEventSupported(LifecycleEventArgs $eventArgs);

   /**
    * Generate or fetch the metadata.
    *
    * @return mixed
    */
   public function getMetadata();
}
