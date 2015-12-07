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

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Doctrine\Common\EventSubscriber,
    Doctrine\Common\Collections\ArrayCollection,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events;

/**
 * TrackableSubscriber handle Trackable entites
 * Listens to lifecycle events
 */
class TrackableSubscriber extends AbstractSubscriber
{
    /**
     * @var callable[]
     */
    private $trackers;

    private $trackableTrait;

    public function __construct(ClassAnalyzer $classAnalyser, $isRecursive, $trackableTrait)
    {
        parent::__construct($classAnalyser, $isRecursive);
        $this->trackers = new ArrayCollection;
        $this->trackableTrait = $trackableTrait;
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $entity->trackCreation($this->generateTrackedEventArgs($eventArgs));
         }
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $entity->trackChange($this->generateTrackedEventArgs($eventArgs));
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($classMetadata, $entity);
        }
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $entity->trackDeletion($this->generateTrackedEventArgs($eventArgs));
        }
    }

    protected function generateTrackedEventArgs(LifecycleEventArgs $eventArgs)
    {
        $metadata = $this->trackers
                         ->filter(function($tracker) use ($eventArgs) { return call_user_func($tracker[0], $eventArgs); })
                         ->map(function($tracker) { return call_user_func($tracker[1]); })
                         ->filter(function($metadata) { return !!$metadata; })
                             ;

        return new TrackedEventArgs($eventArgs, $metadata);
    }

    public function addTracker(TrackerInterface $tracker)
    {
       $this->addCallableTracker($tracker->getName(),
                                 [$tracker, 'isEventSupported'],
                                 [$tracker, 'getMetaData']);
    }

   public function addCallableTracker($name, callable $isEventSupported, callable $tracker)
   {
       $this->trackers[$name] = [$isEventSupported, $tracker];
   }

   public function removeTracker($name)
   {
       if (isset($this->trackers[$name])) {
           unset($this->trackers[$name]);
       }
   }

   /**
     * Checks if entity supports Trackable
     *
     * @param  ReflectionClass $reflClass
     * @return boolean
     */
    protected function isEntitySupported(\ReflectionClass $reflClass)
    {
        return $this->getClassAnalyzer()->hasTrait($reflClass, $this->trackableTrait, $this->isRecursive);
    }

    public function getSubscribedEvents()
    {
        $events = [
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
        ];

        return $events;
    }
}
