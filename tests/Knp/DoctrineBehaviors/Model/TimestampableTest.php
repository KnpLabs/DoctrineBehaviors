<?php

namespace Knp\DoctrineBehaviors\Model;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Knp\DoctrineBehaviors\ORM\Trackable\TrackedEventArgs;

use Doctrine\Common\Collections\ArrayCollection;

class TimestampableTest extends \PHPUnit_Framework_TestCase
{
    public function provider()
    {
        return [
            ['trackCreation', 'createdAt'],
            ['trackChange',   'updatedAt'],
            ['trackDeletion', 'deletedAt'],
        ];
    }

    /**
     * @dataProvider provider
     */ 
    public function testTraitEvent($handler, $field)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $entity = new \BehaviorFixtures\ORM\TimestampableEntity();

        $this->assertNull($accessor->getValue($entity, $field));

        $em = $this->getMockBuilder('Doctrine\\ORM\\EntityManager')
                   ->disableOriginalConstructor()
                   ->getMock();

        $dateTime = new \DateTime;
        
        $eventArgs = new LifecycleEventArgs($entity, $em);
        $trackedEvents = new TrackedEventArgs($eventArgs, new ArrayCollection(['timestamp' => $dateTime]));

        call_user_func([$entity, $handler], $trackedEvents);

        $this->assertSame($dateTime, $accessor->getValue($entity, $field));
    }
}
