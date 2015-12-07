<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Doctrine\Common\EventManager;

require_once 'EntityManagerProvider.php';

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class TimestampableTest extends \PHPUnit_Framework_TestCase
{
    private $subscriber;

    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return array(
            'BehaviorFixtures\\ORM\\TimestampableEntity',
        );
    }

    protected function getEventManager()
    {
        $em = new EventManager;

        $this->subscriber = new \Knp\DoctrineBehaviors\ORM\Timestampable\TimestampableSubscriber(
            new ClassAnalyzer(),
            false,
            'Knp\DoctrineBehaviors\Model\Timestampable\Timestampable'
        );
        
        $em->addEventSubscriber($this->subscriber);

        return $em;
    }

    public function testEventSubscription()
    {
        $this->getEventManager();

        $this->assertEquals([Events::loadClassMetadata], $this->subscriber->getSubscribedEvents());
    }

    public function provideFields()
    {
        return [
            ['createdAt'],
            ['updatedAt'],
            ['deletedAt'],
        ];
    }

    /**
     * @dataProvider provideFields
     */
    public function testMapping($field)
    {
        // We should invoke TimestampableSubscriber::loadClassMetadata directly,
        // However, this would require a rather complex setup.

        $em = $this->getEntityManager($this->getEventManager());

        $metadata = $em->getClassMetadata('BehaviorFixtures\\ORM\\TimestampableEntity');

        $this->assertTrue($metadata->hasField($field), 'Failed asserting that '.$field.' was defined by the Subscriber');
        $this->assertSame('datetime', $metadata->getTypeOfField($field));
    }

    public function testGetMetadata()
    {
        $this->getEventManager();
        
        $this->assertInstanceOf('\DateTime', $this->subscriber->getMetadata());
    }

    public function testGetMetadataNotCached()
    {
        $this->getEventManager();

        $this->assertNotSame($this->subscriber->getMetadata(), $this->subscriber->getMetadata());
    }

    public function testTimestampableIsSupported()
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\TimestampableEntity();

        $eventArgs = new LifecycleEventArgs($entity, $em);

        $this->assertTrue($this->subscriber->isEntitySupported($eventArgs));
    }

    public function testUntimestampableIsNotSupported()
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\UserEntity;

        $eventArgs = new LifecycleEventArgs($entity, $em);

        $this->assertFalse($this->subscriber->isEntitySupported($eventArgs));
    }
}
