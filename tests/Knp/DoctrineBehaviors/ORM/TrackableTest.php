<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Doctrine\Common\EventManager;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Doctrine\ORM\Events;

require_once 'EntityManagerProvider.php';

class TrackableTest extends \PHPUnit_Framework_TestCase
{
    private $subscriber;

    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\TrackableEntity'
        ];
    }

    protected function getEventManager()
    {
        $em = new EventManager;

        $this->subscriber = new \Knp\DoctrineBehaviors\ORM\Trackable\TrackableSubscriber(
            new ClassAnalyzer(),
            false,
            'Knp\DoctrineBehaviors\Model\Trackable\Trackable'
        );

        $em->addEventSubscriber($this->subscriber);

        return $em;
    }

    public function testEventSubscription()
    {
        $this->getEventManager();

        $this->assertContains(Events::prePersist, $this->subscriber->getSubscribedEvents());
        $this->assertContains(Events::preUpdate,  $this->subscriber->getSubscribedEvents());
        $this->assertContains(Events::preRemove,  $this->subscriber->getSubscribedEvents());
    }

    public function provideEvents()
    {
        return [
            ['prePersist', 'creation'],
            ['preUpdate',  'change'  ],
            ['preRemove',  'deletion'],
        ];
    }

    /**
     * @dataProvider provideEvents
     */
    public function testTrackedEvent($event, $tag)
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TrackableEntity();

        // Persist/Flush required to test preUpdate
        $em->persist($entity);
        $em->flush();
        
        $eventArgs = new LifecycleEventArgs($entity, $em);
        call_user_func([$this->subscriber, $event], $eventArgs);

        $this->assertSame($entity->trackedEvent, $tag);
        $this->assertSame($entity->trackedEventArgs->getEntityManager(), $em);
        $this->assertSame($entity->trackedEventArgs->getEntity(), $entity);
    }

    /**
     * @expectedException \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function testPreUpdateRecomputesChanges()
    {
        // The best way would be to have mock EntityManger and UnitOfWork.
        // Then, we could check UnitOfWork::recomputeSingleEntityChangeSet() is
        // actually called on $entity.
        // Unfortunately, the setup for this would be prohibitively complex.
        
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TrackableEntity();

        $eventArgs = new LifecycleEventArgs($entity, $em);
        $this->subscriber->preUpdate($eventArgs);
    }

    /**
     * @dataProvider provideEvents
     */
    public function testUniversalTracker($event)
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TrackableEntity();

        // Persist/Flush required to test preUpdate
        $em->persist($entity);
        $em->flush();

        $tracker = $this->getMock('Knp\\DoctrineBehaviors\\ORM\\Trackable\\TrackerInterface');

        $tracker->expects($this->any())
                ->method('getName')
                ->will($this->returnValue('universal'))
                    ;

        $tracker->expects($this->any())
                ->method('isEventSupported')
                ->will($this->returnValue(true))
                    ;

        $tracker->expects($this->any())
                ->method('getMetadata')
                ->will($this->returnValue($tracker))
                    ;

        $this->subscriber->addTracker($tracker);
        
        $eventArgs = new LifecycleEventArgs($entity, $em);
        call_user_func([$this->subscriber, $event], $eventArgs);

        $this->assertSame($entity->trackedEventArgs->getMetadata()->get('universal'), $tracker);
    }

    /**
     * @dataProvider provideEvents
     */
    public function testTrackerMetadataAreNotCached($event)
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TrackableEntity();

        // Persist/Flush required to test preUpdate
        $em->persist($entity);
        $em->flush();

        $tracker = $this->getMock('Knp\\DoctrineBehaviors\\ORM\\Trackable\\TrackerInterface');

        $tracker->expects($this->any())
                ->method('getName')
                ->will($this->returnValue('repeated'))
                    ;

        $tracker->expects($this->any())
                ->method('isEventSupported')
                ->will($this->returnValue(true))
                    ;

        // Should be at least twice
        $tracker->expects($this->exactly(2))
                ->method('getMetadata')
                ->will($this->returnCallback(function() { return new \DateTime; }))
                    ;

        $this->subscriber->addTracker($tracker);

        // First event
        $eventArgs = new LifecycleEventArgs($entity, $em);
        call_user_func([$this->subscriber, $event], $eventArgs);

        $first = $entity->trackedEventArgs->getMetadata()->get('repeated');

        // Second event
        $eventArgs = new LifecycleEventArgs($entity, $em);
        call_user_func([$this->subscriber, $event], $eventArgs);

        $second = $entity->trackedEventArgs->getMetadata()->get('repeated');

        $this->assertNotSame($first, $second);
    }

    /**
     * @dataProvider provideEvents
     */
    public function testUniversalCallableTracker($event)
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TrackableEntity();

        // Persist/Flush required to test preUpdate
        $em->persist($entity);
        $em->flush();

        $this->subscriber->addCallableTracker('universal', function() { return true; }, function() { return $this->subscriber; });
        
        $eventArgs = new LifecycleEventArgs($entity, $em);
        call_user_func([$this->subscriber, $event], $eventArgs);

        $this->assertSame($entity->trackedEventArgs->getMetadata()->get('universal'), $this->subscriber);
    }

    /**
     * @dataProvider provideEvents
     */
    public function testSkippedTracker($event)
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TrackableEntity();

        // Persist/Flush required to test preUpdate
        $em->persist($entity);
        $em->flush();

        $tracker = $this->getMock('Knp\\DoctrineBehaviors\\ORM\\Trackable\\TrackerInterface');

        $tracker->expects($this->any())
                ->method('getName')
                ->will($this->returnValue('skipped'))
                    ;

        $tracker->expects($this->atLeastOnce())
                ->method('isEventSupported')
                ->will($this->returnValue(false))
                    ;

        $tracker->expects($this->any())
                ->method('getMetadata')
                ->will($this->returnValue($tracker))
                    ;

        $this->subscriber->addTracker($tracker);
        
        $eventArgs = new LifecycleEventArgs($entity, $em);
        call_user_func([$this->subscriber, $event], $eventArgs);

        $this->assertFalse($entity->trackedEventArgs->getMetadata()->containsKey('skipped'));
    }

    /**
     * @dataProvider provideEvents
     */
    public function testSkippedCallableTracker($event)
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TrackableEntity();

        // Persist/Flush required to test preUpdate
        $em->persist($entity);
        $em->flush();

        $tracker = $this->getMock('Knp\\DoctrineBehaviors\\ORM\\Trackable\\TrackerInterface');

        $tracker->expects($this->atLeastOnce())
                ->method('isEventSupported')
                ->will($this->returnValue(false))
                    ;

        $this->subscriber->addCallableTracker('skipped', [$tracker, 'isEventSupported'], function() { return $this->subscriber; });
        
        $eventArgs = new LifecycleEventArgs($entity, $em);
        call_user_func([$this->subscriber, $event], $eventArgs);

        $this->assertFalse($entity->trackedEventArgs->getMetadata()->containsKey('empty'));
    }

    /**
     * @dataProvider provideEvents
     */
    public function testEmptyTracker($event)
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TrackableEntity();

        // Persist/Flush required to test preUpdate
        $em->persist($entity);
        $em->flush();

        $tracker = $this->getMock('Knp\\DoctrineBehaviors\\ORM\\Trackable\\TrackerInterface');

        $tracker->expects($this->any())
                ->method('getName')
                ->will($this->returnValue('empty'))
                    ;

        $tracker->expects($this->any())
                ->method('isEventSupported')
                ->will($this->returnValue(true))
                    ;

        $tracker->expects($this->atLeastOnce())
                ->method('getMetadata')
                ->will($this->returnValue(null))
                    ;

        $this->subscriber->addTracker($tracker);
        
        $eventArgs = new LifecycleEventArgs($entity, $em);
        call_user_func([$this->subscriber, $event], $eventArgs);

        $this->assertFalse($entity->trackedEventArgs->getMetadata()->containsKey('empty'));
    }

    /**
     * @dataProvider provideEvents
     */
    public function testEmptyCallableTracker($event)
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TrackableEntity();

        // Persist/Flush required to test preUpdate
        $em->persist($entity);
        $em->flush();

        $tracker = $this->getMock('Knp\\DoctrineBehaviors\\ORM\\Trackable\\TrackerInterface');

        $tracker->expects($this->atLeastOnce())
                ->method('getMetadata')
                ->will($this->returnValue(null))
                    ;

        $this->subscriber->addCallableTracker('empty', function() { return true; }, [$tracker, 'getMetadata']);
        
        $eventArgs = new LifecycleEventArgs($entity, $em);
        call_user_func([$this->subscriber, $event], $eventArgs);

        $this->assertFalse($entity->trackedEventArgs->getMetadata()->containsKey('empty'));
    }

    /**
     * @dataProvider provideEvents
     */
    public function testRemovedTracker($event)
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TrackableEntity();

        // Persist/Flush required to test preUpdate
        $em->persist($entity);
        $em->flush();

        $tracker = $this->getMock('Knp\\DoctrineBehaviors\\ORM\\Trackable\\TrackerInterface');

        $tracker->expects($this->any())
                ->method('getName')
                ->will($this->returnValue('removed'))
                    ;

        $tracker->expects($this->never())
                ->method('isEventSupported')
                ->will($this->returnValue(true))
                    ;

        $tracker->expects($this->never())
                ->method('getMetadata')
                ->will($this->returnValue(null))
                    ;

        $this->subscriber->addTracker($tracker);
        $this->subscriber->removeTracker('removed');
        
        $eventArgs = new LifecycleEventArgs($entity, $em);
        call_user_func([$this->subscriber, $event], $eventArgs);

        $this->assertFalse($entity->trackedEventArgs->getMetadata()->containsKey('removed'));
    }

    /**
     * @dataProvider provideEvents
     */
    public function testRemovedCallableTracker($event)
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TrackableEntity();

        // Persist/Flush required to test preUpdate
        $em->persist($entity);
        $em->flush();

        $tracker = $this->getMock('Knp\\DoctrineBehaviors\\ORM\\Trackable\\TrackerInterface');

        $tracker->expects($this->never())
                ->method('isEventSupported')
                ->will($this->returnValue(true))
                    ;

        $tracker->expects($this->never())
                ->method('getMetadata')
                ->will($this->returnValue(null))
                    ;

        $this->subscriber->addCallableTracker('removed', [$tracker, 'isEventSupported'], [$tracker, 'getMetadata']);
        $this->subscriber->removeTracker('removed');
        
        $eventArgs = new LifecycleEventArgs($entity, $em);
        call_user_func([$this->subscriber, $event], $eventArgs);

        $this->assertFalse($entity->trackedEventArgs->getMetadata()->containsKey('removed'));
    }
}
