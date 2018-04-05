<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Doctrine\Common\EventManager;

require_once 'EntityManagerProvider.php';

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

    public function testCreate()
    {
        $em = $this->getEntityManager($this->getEventManager('user'));

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $logger = $this->getSqlLogger();
        $logger->enabled = true;

        $em->persist($entity);
        $em->flush();

        $this->assertCount(3, $logger->queries);
        $this->assertEquals('"START TRANSACTION"', $logger->queries[1]['sql']);
        $this->assertEquals('INSERT INTO BlameableEntity (title, createdBy, updatedBy, deletedBy) VALUES (?, ?, ?, ?)', $logger->queries[2]['sql']);
        $this->assertEquals('"COMMIT"', $logger->queries[3]['sql']);

        $this->assertEquals('user', $entity->getCreatedBy());
        $this->assertEquals('user', $entity->getUpdatedBy());
        $this->assertNull($entity->getDeletedBy());
    }

    public function testUpdate()
    {
        $em = $this->getEntityManager($this->getEventManager('user'));

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $createdBy = $entity->getCreatedBy();
        $em->clear();

        $subscribers = $em->getEventManager()->getListeners()['preUpdate'];
        $subscriber = array_pop($subscribers);
        $subscriber->setUser('user2');

        $logger = $this->getSqlLogger();
        $logger->enabled = true;

        $entity = $em->getRepository('BehaviorFixtures\ORM\BlameableEntity')->find($id);
        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $em->flush();
        $em->clear();

        $this->assertCount(4, $logger->queries);
        $this->assertEquals('"START TRANSACTION"', $logger->queries[2]['sql']);
        $this->assertEquals('UPDATE BlameableEntity SET title = ?, updatedBy = ? WHERE id = ?', $logger->queries[3]['sql']);
        $this->assertEquals('"COMMIT"', $logger->queries[4]['sql']);

        //$entity = $em->getRepository('BehaviorFixtures\ORM\BlameableEntity')->find($id);
        $this->assertEquals($createdBy, $entity->getCreatedBy(), 'createdBy is constant');
        $this->assertEquals('user2', $entity->getUpdatedBy());

        $this->assertNotEquals(
            $entity->getCreatedBy(),
            $entity->getUpdatedBy(),
            'createBy and updatedBy have diverged since new update'
        );
    }

    public function testRemove()
    {
        $em = $this->getEntityManager($this->getEventManager('user'));

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $subscribers = $em->getEventManager()->getListeners()['preRemove'];
        $subscriber = array_pop($subscribers);
        $subscriber->setUser('user3');

        $entity = $em->getRepository('BehaviorFixtures\ORM\BlameableEntity')->find($id);
        $em->remove($entity);
        $em->flush();
        $em->clear();

        $this->assertEquals('user3', $entity->getDeletedBy());
    }

    public function testSubscriberWithUserCallback()
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

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $createdBy = $entity->getCreatedBy();
        $this->subscriber->setUser($user2); // switch user for update

        $entity = $em->getRepository('BehaviorFixtures\ORM\BlameableEntity')->find($id);
        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $em->flush();
        $em->clear();

        $this->assertInstanceOf('BehaviorFixtures\\ORM\\UserEntity', $entity->getCreatedBy(), 'createdBy is a user object');
        $this->assertEquals($createdBy->getUsername(), $entity->getCreatedBy()->getUsername(), 'createdBy is constant');
        $this->assertEquals($user2->getUsername(), $entity->getUpdatedBy()->getUsername());

        $this->assertNotEquals(
            $entity->getCreatedBy(),
            $entity->getUpdatedBy(),
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

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertNull($entity->getCreatedBy(), 'createdBy is a not updated because not a user entity object');
        $this->assertNull($entity->getUpdatedBy(), 'updatedBy is a not updated because not a user entity object');
    }

    /**
     * @test
     */
    public function should_only_persist_user_string()
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $em   = $this->getEntityManager($this->getEventManager($user));

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertNull($entity->getCreatedBy(), 'createdBy is a not updated because not a user entity object');
        $this->assertNull($entity->getUpdatedBy(), 'updatedBy is a not updated because not a user entity object');
    }
}
