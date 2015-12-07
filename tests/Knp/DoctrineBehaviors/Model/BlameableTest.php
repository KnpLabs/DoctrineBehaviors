<?php

namespace Knp\DoctrineBehaviors\Model;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Knp\DoctrineBehaviors\ORM\Trackable\TrackedEventArgs;

use Doctrine\Common\Collections\ArrayCollection;

class BlameableTest extends \PHPUnit_Framework_TestCase
{
    public function provider()
    {
        return [
            ['trackCreation', 'createdBy'],
            ['trackChange',   'updatedBy'],
            ['trackDeletion', 'deletedBy'],
        ];
    }

    /**
     * @dataProvider provider
     */ 
    public function testTraitEvent($handler, $field)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $this->assertNull($accessor->getValue($entity, $field));

        $em = $this->getMockBuilder('Doctrine\\ORM\\EntityManager')
                   ->disableOriginalConstructor()
                   ->getMock();
        
        $eventArgs = new LifecycleEventArgs($entity, $em);
        $trackedEvents = new TrackedEventArgs($eventArgs, new ArrayCollection(['user' => 'foo']));

        call_user_func([$entity, $handler], $trackedEvents);

        $this->assertSame('foo', $accessor->getValue($entity, $field));
    }
}
