<?php

declare(strict_types=1);

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

require_once 'EntityManagerProvider.php';

class BlameableTest extends \PHPUnit\Framework\TestCase
{
    use EntityManagerProvider;

    private $subscriber;

    public function testCreate(): void
    {
        $em = $this->getEntityManager($this->getEventManager('user'));

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertSame('user', $entity->getCreatedBy());
        $this->assertSame('user', $entity->getUpdatedBy());
        $this->assertNull($entity->getDeletedBy());
    }

    public function testUpdate(): void
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

        $entity = $em->getRepository('BehaviorFixtures\ORM\BlameableEntity')->find($id);
        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $em->flush();
        $em->clear();

        //$entity = $em->getRepository('BehaviorFixtures\ORM\BlameableEntity')->find($id);
        $this->assertSame($createdBy, $entity->getCreatedBy(), 'createdBy is constant');
        $this->assertSame('user2', $entity->getUpdatedBy());

        $this->assertNotSame(
            $entity->getCreatedBy(),
            $entity->getUpdatedBy(),
            'createBy and updatedBy have diverged since new update'
        );
    }

    public function testRemove(): void
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

        $this->assertSame('user3', $entity->getDeletedBy());
    }

    public function testSubscriberWithUserCallback(): void
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $user->setUsername('user');

        $user2 = new \BehaviorFixtures\ORM\UserEntity();
        $user2->setUsername('user2');

        $userCallback = function () use ($user) {
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
        $this->assertSame($createdBy->getUsername(), $entity->getCreatedBy()->getUsername(), 'createdBy is constant');
        $this->assertSame($user2->getUsername(), $entity->getUpdatedBy()->getUsername());

        $this->assertNotSame(
            $entity->getCreatedBy(),
            $entity->getUpdatedBy(),
            'createBy and updatedBy have diverged since new update'
        );
    }

    public function testShouldOnlyPersistUserEntity(): void
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $user->setUsername('user');

        $userCallback = function () use ($user) {
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

    public function testShouldOnlyPersistUserString(): void
    {
        $user = new \BehaviorFixtures\ORM\UserEntity();
        $em = $this->getEntityManager($this->getEventManager($user));

        $entity = new \BehaviorFixtures\ORM\BlameableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertNull($entity->getCreatedBy(), 'createdBy is a not updated because not a user entity object');
        $this->assertNull($entity->getUpdatedBy(), 'updatedBy is a not updated because not a user entity object');
    }

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\BlameableEntity',
            'BehaviorFixtures\\ORM\\UserEntity',
        ];
    }

    protected function getEventManager($user = null, $userCallback = null, $userEntity = null)
    {
        $em = new EventManager();

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
}
