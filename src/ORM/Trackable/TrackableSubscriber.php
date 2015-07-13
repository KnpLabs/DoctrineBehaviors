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

use Knp\DoctrineBehaviors\Model\Trackable\TrackerInterface;

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
            $this->trackCreation($this->generateTrackedEventArgs($eventArgs));
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($entity);
         }
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $this->trackChange($this->generateTrackedEventArgs($eventArgs));
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($entity);
        }
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass)) {
            $this->trackDeletion($this->generateTrackedEventArgs($eventArgs));
            $em->getUnitOfWork()->recomputeSingleEntityChangeSet($entity);
        }
    }

    protected function generateTrackedEventArgs(LifecycleEventArgs $eventArgs)
    {
        $metada = $this->trackers
                       ->filter(function($tracker) use ($eventArgs) { return call_user_func($tracker[0], $eventArgs); })
                       ->map(function($tracker) { return call_user_func($tracker[1]); })
                       ->filter(function($metadata) { return !!$metadata; })
                          ;

        return new TrackedEventArgs($args, $metadata);
    }

    public function addTracker(TrackerInterface $tracker)
    {
       $this->addCallableTracker($tracker->getName(),
                                 [$tracker, 'isEntitySupported'],
                                 [$tracker, 'getMetaData']);
    }

   public function addCallableTracker($name, callable $isEntitySupported, callable $tracker)
   {
       $this->trackers[$name] = [$isEntitySupported, $tracker];
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
