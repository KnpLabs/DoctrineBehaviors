<?php

declare(strict_types=1);

namespace Tests\Knp\DoctrineBehaviors\ORM;

use BehaviorFixtures\ORM\TimestampableEntity;
use Datetime;
use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Knp\DoctrineBehaviors\ORM\Timestampable\TimestampableSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EntityManagerProvider.php';

class TimestampableTest extends TestCase
{
    use EntityManagerProvider;

    public function testItShouldInitializeCreateAndUpdateDatetimeWhenCreated(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new TimestampableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertInstanceOf(Datetime::class, $entity->getCreatedAt());
        $this->assertInstanceOf(Datetime::class, $entity->getUpdatedAt());

        $this->assertSame(
            $entity->getCreatedAt(),
            $entity->getUpdatedAt(),
            'On creation, createdAt and updatedAt are the same'
        );
    }

    public function testItShouldModifyUpdateDatetimeWhenUpdatedButNotTheCreationDatetime(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new TimestampableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->refresh($entity);
        $id = $entity->getId();
        $createdAt = $entity->getCreatedAt();
        $entityManager->clear();

        // wait for a second:
        sleep(1);

        $entity = $entityManager->getRepository(TimestampableEntity::class)->find($id);
        $entity->setTitle('test');
        $entityManager->flush();
        $entityManager->clear();

        $entity = $entityManager->getRepository(TimestampableEntity::class)->find($id);
        $this->assertSame($createdAt, $entity->getCreatedAt(), 'createdAt is constant');

        $this->assertNotSame(
            $entity->getCreatedAt(),
            $entity->getUpdatedAt(),
            'createat and updatedAt have diverged since new update'
        );
    }

    public function testItShouldReturnTheSameDatetimeWhenNotUpdated(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new TimestampableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->refresh($entity);
        $id = $entity->getId();
        $createdAt = $entity->getCreatedAt();
        $updatedAt = $entity->getUpdatedAt();
        $entityManager->clear();

        sleep(1);

        $entity = $entityManager->getRepository(TimestampableEntity::class)->find($id);
        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $this->assertSame($entity->getCreatedAt(), $createdAt, 'Creation timestamp has changed');

        $this->assertSame($entity->getUpdatedAt(), $updatedAt, 'Update timestamp has changed');
    }

    public function testItShouldModifyUpdateDatetimeOnlyOnce(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new TimestampableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->refresh($entity);
        $id = $entity->getId();
        $createdAt = $entity->getCreatedAt();
        $entityManager->clear();

        sleep(1);

        $entity = $entityManager->getRepository(TimestampableEntity::class)->find($id);
        $entity->setTitle('test');
        $entityManager->flush();
        $updatedAt = $entity->getUpdatedAt();

        sleep(1);

        $entityManager->flush();

        $this->assertSame($entity->getCreatedAt(), $createdAt, 'Creation timestamp has changed');

        $this->assertSame($entity->getUpdatedAt(), $updatedAt, 'Update timestamp has changed');
    }

    protected function getUsedEntityFixtures()
    {
        return [TimestampableEntity::class];
    }

    protected function getEventManager(): EventManager
    {
        $eventManager = new EventManager();

        $eventManager->addEventSubscriber(
            new TimestampableSubscriber(new ClassAnalyzer(), false, Timestampable::class, 'datetime')
        );

        return $eventManager;
    }
}
