<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Datetime;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TimestampableEntity;

final class TimestampableTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository|EntityRepository
     */
    private $timestampableRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->timestampableRepository = $this->entityManager->getRepository(TimestampableEntity::class);
    }

    public function testItShouldInitializeCreateAndUpdateDatetimeWhenCreated(): void
    {
        $entity = new TimestampableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

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
        $entity = new TimestampableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->refresh($entity);
        $id = $entity->getId();
        $createdAt = $entity->getCreatedAt();
        $this->entityManager->clear();

        // wait for a second:
        sleep(1);

        /** @var TimestampableEntity $entity */
        $entity = $this->timestampableRepository->find($id);

        $entity->setTitle('test');
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var TimestampableEntity $entity */
        $entity = $this->timestampableRepository->find($id);
        $this->assertEquals($createdAt, $entity->getCreatedAt(), 'createdAt is constant');

        $this->assertNotSame(
            $entity->getCreatedAt(),
            $entity->getUpdatedAt(),
            'createat and updatedAt have diverged since new update'
        );
    }

    public function testItShouldReturnTheSameDatetimeWhenNotUpdated(): void
    {
        $entity = new TimestampableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->refresh($entity);

        $id = $entity->getId();

        $createdAt = $entity->getCreatedAt();
        $updatedAt = $entity->getUpdatedAt();

        $this->entityManager->clear();

        sleep(1);

        /** @var TimestampableEntity $entity */
        $entity = $this->timestampableRepository->find($id);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->assertEquals($entity->getCreatedAt(), $createdAt, 'Creation timestamp has changed');
        $this->assertEquals($entity->getUpdatedAt(), $updatedAt, 'Update timestamp has changed');
    }

    public function testItShouldModifyUpdateDatetimeOnlyOnce(): void
    {
        $entity = new TimestampableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->refresh($entity);

        $id = $entity->getId();
        $createdAt = $entity->getCreatedAt();

        $this->entityManager->clear();

        sleep(1);

        /** @var TimestampableEntity $entity */
        $entity = $this->timestampableRepository->find($id);

        $entity->setTitle('test');
        $this->entityManager->flush();

        $updatedAt = $entity->getUpdatedAt();

        sleep(1);

        $this->entityManager->flush();

        // different object, but values are the same
        $this->assertEquals($entity->getCreatedAt(), $createdAt, 'Creation timestamp has changed');
        $this->assertEquals($entity->getUpdatedAt(), $updatedAt, 'Update timestamp has changed');
    }
}
