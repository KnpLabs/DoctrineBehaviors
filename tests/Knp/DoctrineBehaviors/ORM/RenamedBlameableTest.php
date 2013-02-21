<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultBlameableTest.php';

class RenamedBlameableTest extends DefaultBlameableTest
{
    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedBlameableEntity";
    }

    public function testCreate()
    {
        $em = $this->getEntityManager($this->getEventManager('user'));

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertEquals('user', $entity->getTraitCreatedBy());
        $this->assertEquals('user', $entity->getTraitUpdatedBy());
    }

    public function testUpdate()
    {
        $em = $this->getEntityManager($this->getEventManager('user'));

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $createdBy = $entity->getTraitCreatedBy();
        $em->clear();

        $listeners = $em->getEventManager()->getListeners()['preUpdate'];
        $listener = array_pop($listeners);
        $listener->setUser('user2');

        $entity = $em->getRepository($this->getTestedEntityClass())->find($id);
        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $em->flush();
        $em->clear();

        //$entity = $em->getRepository($this->getTestedEntityClass())->find($id);
        $this->assertEquals($createdBy, $entity->getTraitCreatedBy(), 'createdBy is constant');
        $this->assertEquals('user2', $entity->getTraitUpdatedBy());

        $this->assertNotEquals(
            $entity->getTraitCreatedBy(),
            $entity->getTraitUpdatedBy(),
            'createBy and updatedBy have diverged since new update'
        );
    }

    public function testRemove()
    {
        $em = $this->getEntityManager($this->getEventManager('user'));

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $listeners = $em->getEventManager()->getListeners()['preRemove'];
        $listener = array_pop($listeners);
        $listener->setUser('user3');

        $entity = $em->getRepository($this->getTestedEntityClass())->find($id);
        $em->remove($entity);
        $em->flush();
        $em->clear();

        $this->assertEquals('user3', $entity->getTraitDeletedBy());
    }

    public function testListenerWithUserCallback()
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $user->setUsername('user');

        $user2 = new \BehaviorFixtures\ORM\UserEntity();
        $user2->setUsername('user2');

        $userCallback = function() use($user) {
            return $user;
        };

        $em = $this->getEntityManager($this->getEventManager(null, $userCallback, get_class($user)));
        $em->persist($user);
        $em->persist($user2);
        $em->flush();

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $createdBy = $entity->getTraitCreatedBy();
        $this->listener->setUser($user2); // switch user for update

        $entity = $em->getRepository($this->getTestedEntityClass())->find($id);
        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $em->flush();
        $em->clear();

        $this->assertInstanceOf('BehaviorFixtures\\ORM\\UserEntity', $entity->getTraitCreatedBy(), 'createdBy is a user object');
        $this->assertEquals($createdBy->getUsername(), $entity->getTraitCreatedBy()->getUsername(), 'createdBy is constant');
        $this->assertEquals($user2->getUsername(), $entity->getTraitUpdatedBy()->getUsername());

        $this->assertNotEquals(
            $entity->getTraitCreatedBy(),
            $entity->getTraitUpdatedBy(),
            'createBy and updatedBy have diverged since new update'
        );
    }

    /**
     * @test
     */
    public function should_only_persist_user_entity()
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $user->setUsername('user');

        $userCallback = function() use($user) {
            return $user;
        };

        $em = $this->getEntityManager($this->getEventManager('anon.', $userCallback, get_class($user)));
        $em->persist($user);
        $em->flush();

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertNull($entity->getTraitCreatedBy(), 'createdBy is a not updated because not a user entity object');
        $this->assertNull($entity->getTraitUpdatedBy(), 'updatedBy is a not updated because not a user entity object');
    }

    /**
     * @test
     */
    public function should_only_persist_user_string()
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $em   = $this->getEntityManager($this->getEventManager($user));

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertNull($entity->getTraitCreatedBy(), 'createdBy is a not updated because not a user entity object');
        $this->assertNull($entity->getTraitUpdatedBy(), 'updatedBy is a not updated because not a user entity object');
    }

}
