<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\ORM\Blameable\BlameableSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\BlameableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\UserEntity;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EntityManagerProvider.php';

class BlameableTest extends TestCase
{
    use EntityManagerProvider;

    private $subscriber;

    public function testCreate(): void
    {
        $entityManager = $this->getEntityManager($this->getEventManager('user'));

        $entity = new BlameableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertSame('user', $entity->getCreatedBy());
        $this->assertSame('user', $entity->getUpdatedBy());
        $this->assertNull($entity->getDeletedBy());
    }

    public function testUpdate(): void
    {
        $entityManager = $this->getEntityManager($this->getEventManager('user'));

        $entity = new BlameableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();
        $id = $entity->getId();
        $createdBy = $entity->getCreatedBy();
        $entityManager->clear();

        $subscribers = $entityManager->getEventManager()->getListeners()['preUpdate'];
        $subscriber = array_pop($subscribers);
        $subscriber->setUser('user2');

        $entity = $entityManager->getRepository(BlameableEntity::class)->find($id);
        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $entityManager->flush();
        $entityManager->clear();

        //$entity = $entityManager->getRepository('Knp\DoctrineBehaviors\Tests\Fixtures\ORM\BlameableEntity')->find($id);
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
        $entityManager = $this->getEntityManager($this->getEventManager('user'));

        $entity = new BlameableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();
        $id = $entity->getId();
        $entityManager->clear();

        $subscribers = $entityManager->getEventManager()->getListeners()['preRemove'];
        $subscriber = array_pop($subscribers);
        $subscriber->setUser('user3');

        $entity = $entityManager->getRepository(BlameableEntity::class)->find($id);
        $entityManager->remove($entity);
        $entityManager->flush();
        $entityManager->clear();

        $this->assertSame('user3', $entity->getDeletedBy());
    }

    public function testSubscriberWithUserCallback(): void
    {
        $user = new UserEntity();
        $user->setUsername('user');

        $user2 = new UserEntity();
        $user2->setUsername('user2');

        $userCallback = function () use ($user) {
            return $user;
        };

        $entityManager = $this->getEntityManager($this->getEventManager(null, $userCallback, get_class($user)));
        $entityManager->persist($user);
        $entityManager->persist($user2);
        $entityManager->flush();

        $entity = new BlameableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();
        $id = $entity->getId();
        $createdBy = $entity->getCreatedBy();
        $this->subscriber->setUser($user2); // switch user for update

        $entity = $entityManager->getRepository(BlameableEntity::class)->find($id);
        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $entityManager->flush();
        $entityManager->clear();

        $this->assertInstanceOf(UserEntity::class, $entity->getCreatedBy(), 'createdBy is a user object');
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
        $user = new UserEntity();
        $user->setUsername('user');

        $userCallback = function () use ($user) {
            return $user;
        };

        $entityManager = $this->getEntityManager($this->getEventManager('anon.', $userCallback, get_class($user)));
        $entityManager->persist($user);
        $entityManager->flush();

        $entity = new BlameableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertNull($entity->getCreatedBy(), 'createdBy is a not updated because not a user entity object');
        $this->assertNull($entity->getUpdatedBy(), 'updatedBy is a not updated because not a user entity object');
    }

    public function testShouldOnlyPersistUserString(): void
    {
        $user = new UserEntity();
        $entityManager = $this->getEntityManager($this->getEventManager($user));

        $entity = new BlameableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertNull($entity->getCreatedBy(), 'createdBy is a not updated because not a user entity object');
        $this->assertNull($entity->getUpdatedBy(), 'updatedBy is a not updated because not a user entity object');
    }

    protected function getUsedEntityFixtures()
    {
        return [BlameableEntity::class, UserEntity::class];
    }

    protected function getEventManager($user = null, $userCallback = null, $userEntity = null)
    {
        $eventManager = new EventManager();

        $this->subscriber = new BlameableSubscriber(
            new ClassAnalyzer(),
            false,
            Blameable::class,
            $userCallback,
            $userEntity
        );
        $this->subscriber->setUser($user);

        $eventManager->addEventSubscriber($this->subscriber);

        return $eventManager;
    }
}
