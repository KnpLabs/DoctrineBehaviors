<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

require_once 'EntityManagerProvider.php';

class BlameableTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\BlameableEntity',
            'BehaviorFixtures\\ORM\\UserEntity'
        ];
    }

    private function getSecurityContext($user)
    {
        $mock = $this
            ->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $token = new UsernamePasswordToken($user, 'password', '_');
        $mock
            ->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token))
        ;

        return $mock;
    }

    protected function getEventManager($securityContext = null, $userEntity = null)
    {
        $em = new EventManager;

        $listener = new \Knp\DoctrineBehaviors\ORM\Blameable\BlameableListener(
            $securityContext,
            $userEntity
        );
        $listener->setUser('user');

        $em->addEventSubscriber($listener);

        return $em;
    }

    public function testCreate()
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertEquals('user', $entity->getCreatedBy());
        $this->assertEquals('user', $entity->getUpdatedBy());
    }

    public function testUpdate()
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $createdBy = $entity->getCreatedBy();
        $em->clear();

        $listeners = $em->getEventManager()->getListeners()['preUpdate'];
        $listener = array_pop($listeners);
        $listener->setUser('user2');

        $entity = $em->getRepository('BehaviorFixtures\ORM\BlameableEntity')->find($id);
        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $em->flush();
        $em->clear();

        //$entity = $em->getRepository('BehaviorFixtures\ORM\BlameableEntity')->find($id);
        $this->assertEquals($createdBy, $entity->getCreatedBy(), 'createdBy is constant');
        $this->assertEquals('user2', $entity->getUpdatedBy());

        $this->assertNotEquals(
            $entity->getCreatedBy(),
            $entity->getUpdatedBy(),
            'createBy and updatedBy have diverged since new update'
        );
    }

    public function testListenerWithSecurityContext()
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $user->setUsername('user');

        $securityContext = $this->getSecurityContext($user);
        $em = $this->getEntityManager($this->getEventManager($securityContext, get_class($user)));
        $em->persist($user);
        $em->flush();

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $listeners = $em->getEventManager()->getListeners()['preUpdate'];
        $listener = array_pop($listeners);
        $listener->setUser(null); // use securityContext

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $createdBy = $entity->getCreatedBy();
        $em->clear();

        $entity = $em->getRepository('BehaviorFixtures\ORM\BlameableEntity')->find($id);
        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $em->flush();
        $em->clear();

        $this->assertInstanceOf('BehaviorFixtures\\ORM\\UserEntity', $entity->getCreatedBy(), 'createdBy is constant');
        $this->assertEquals($createdBy->getUsername(), $entity->getCreatedBy()->getUsername(), 'createdBy is constant');
        $this->assertEquals($user->getUsername(), $entity->getUpdatedBy()->getUsername());

        $this->assertNotEquals(
            $entity->getCreatedBy(),
            $entity->getUpdatedBy(),
            'createBy and updatedBy have diverged since new update'
        );
    }
}
