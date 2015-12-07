<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Doctrine\Common\EventManager;

require_once 'EntityManagerProvider.php';

use Doctrine\ORM\Event\LifecycleEventArgs;

use Doctrine\ORM\Events;

class BlameableTest extends \PHPUnit_Framework_TestCase
{
    private $subscriber;

    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\BlameableEntity',
            'BehaviorFixtures\\ORM\\UserEntity'
        ];
    }

    protected function getEventManager($user = null, $userCallback = null, $userEntity = null)
    {
        $em = new EventManager;

        $this->subscriber = new \Knp\DoctrineBehaviors\ORM\Blameable\BlameableSubscriber(
            new ClassAnalyzer(),
            false,
            'Knp\DoctrineBehaviors\Model\Blameable\Blameable',
            $userCallback,
            $userEntity
        );
        $this->subscriber->setUser($user);

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
            ['createdBy'],
            ['updatedBy'],
            ['deletedBy'],
        ];
    }

    /**
     * @dataProvider provideFields
     */
    public function testMappingStringUser($field)
    {
        // We should invoke BlameableSubscriber::loadClassMetadata directly,
        // However, this would require a rather complex setup.

        $em = $this->getEntityManager($this->getEventManager('user'));

        $metadata = $em->getClassMetadata('BehaviorFixtures\\ORM\\BlameableEntity');

        $this->assertTrue($metadata->hasField($field), 'Failed asserting that '.$field.' was defined by the Subscriber');
        $this->assertSame('string', $metadata->getTypeOfField($field));
    }

    /**
     * @dataProvider provideFields
     */
    public function testMappingUserEntity($field)
    {
        // We should invoke BlameableSubscriber::loadClassMetadata directly,
        // However, this would require a rather complex setup.

        $em = $this->getEntityManager($this->getEventManager('user', function() { }, 'BehaviorFixtures\\ORM\\UserEntity'));

        $metadata = $em->getClassMetadata('BehaviorFixtures\\ORM\\BlameableEntity');

        $this->assertTrue($metadata->hasAssociation($field), 'Failed asserting that '.$field.' was defined as an association by the Subscriber');
        $this->assertSame('BehaviorFixtures\\ORM\\UserEntity', $metadata->getAssociationTargetClass($field));
    }

    public function testGetUserSetUser()
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $this->getEventManager($user);

        $this->assertSame($user, $this->subscriber->getUser());
    }

    public function testGetUserCallback()
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $this->getEventManager(null, function() use ($user) { return $user; });

        $this->assertSame($user, $this->subscriber->getUser());
    }

    public function testGetUserSetUserOverrideCallback()
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $user2 = new \BehaviorFixtures\ORM\UserEntity();
        $this->getEventManager($user, function() use ($user2) { return $user2; });

        $this->assertSame($user, $this->subscriber->getUser());
    }

    public function testGetMetadataValidUserString()
    {
        $this->getEventManager('user');
        $this->assertSame('user', $this->subscriber->getMetadata());
    }
    
    public function testGetMetadataInvalidUserString()
    {
        $this->getEventManager('user', null, 'BehaviorFixtures\\ORM\\UserEntity');
        $this->assertNull($this->subscriber->getMetadata());
    }
    
    public function testGetMetadataValidUserEntity()
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $this->getEventManager($user, null, get_class($user));
        $this->assertSame($user, $this->subscriber->getMetadata());
    }
    
    public function testGetMetadataInvalidUserEntity()
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $this->getEventManager($user);
        $this->assertNull($this->subscriber->getMetadata());
    }

    public function testGetMetadataInvalidUserEntityClass()
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $this->getEventManager($user, null, get_class($user).'2');
        $this->assertNull($this->subscriber->getMetadata());
    }

    public function testBlameableIsSupported()
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $eventArgs = new LifecycleEventArgs($entity, $em);

        $this->assertTrue($this->subscriber->isEventSupported($eventArgs));
    }

    public function testDisabledBlameableIsNotSupported()
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\BlameableEntity(false);

        $eventArgs = new LifecycleEventArgs($entity, $em);

        $this->assertFalse($this->subscriber->isEventSupported($eventArgs));
    }

    public function testUnblameableIsNotSupported()
    {
        $em = $this->getEntityManager($this->getEventManager());
        $entity = new \BehaviorFixtures\ORM\UserEntity;

        $eventArgs = new LifecycleEventArgs($entity, $em);

        $this->assertFalse($this->subscriber->isEventSupported($eventArgs));
    }
}
