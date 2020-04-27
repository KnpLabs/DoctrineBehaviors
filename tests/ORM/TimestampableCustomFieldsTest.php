<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Datetime;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TimestampableCustomFieldsEntity;

final class TimestampableCustomFieldsTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository|EntityRepository
     */
    private $timestampableRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->timestampableRepository = $this->entityManager->getRepository(TimestampableCustomFieldsEntity::class);
    }

    public function testItShouldInitializeCreateAndUpdateDatetimeWhenCreated(): void
    {
        $entity = new TimestampableCustomFieldsEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertInstanceOf(Datetime::class, $entity->getServerCreatedAt());
        $this->assertInstanceOf(Datetime::class, $entity->getServerUpdatedAt());

        $this->assertSame(
            $entity->getServerCreatedAt(),
            $entity->getServerUpdatedAt(),
            'On creation, serverCreatedAt and serverUpdatedAt are the same'
        );
    }

    public function testItShouldModifyUpdateDatetimeWhenUpdatedButNotTheCreationDatetime(): void
    {
        $entity = new TimestampableCustomFieldsEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->refresh($entity);
        $id = $entity->getId();
        $createdAt = $entity->getServerCreatedAt();
        $this->entityManager->clear();

        // wait for a second:
        sleep(1);

        /** @var TimestampableCustomFieldsEntity $entity */
        $entity = $this->timestampableRepository->find($id);

        $entity->setTitle('test');
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var TimestampableCustomFieldsEntity $entity */
        $entity = $this->timestampableRepository->find($id);
        $this->assertSame($createdAt, $entity->getServerCreatedAt(), 'serverCreatedAt is constant');

        $this->assertNotSame(
            $entity->getServerCreatedAt(),
            $entity->getServerUpdatedAt(),
            'serverCreatedAt and serverUpdatedAt have diverged since new update'
        );
    }

    public function testItShouldReturnTheSameDatetimeWhenNotUpdated(): void
    {
        $entity = new TimestampableCustomFieldsEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->refresh($entity);

        $id = $entity->getId();

        $createdAt = $entity->getServerCreatedAt();
        $updatedAt = $entity->getServerUpdatedAt();

        $this->entityManager->clear();

        sleep(1);

        /** @var TimestampableCustomFieldsEntity $entity */
        $entity = $this->timestampableRepository->find($id);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->assertSame($entity->getServerCreatedAt(), $createdAt, 'Creation timestamp has changed');
        $this->assertSame($entity->getServerUpdatedAt(), $updatedAt, 'Update timestamp has changed');
    }

    public function testItShouldModifyUpdateDatetimeOnlyOnce(): void
    {
        $entity = new TimestampableCustomFieldsEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->refresh($entity);

        $id = $entity->getId();
        $createdAt = $entity->getServerCreatedAt();

        $this->entityManager->clear();

        sleep(1);

        /** @var TimestampableCustomFieldsEntity $entity */
        $entity = $this->timestampableRepository->find($id);

        $entity->setTitle('test');
        $this->entityManager->flush();

        $updatedAt = $entity->getServerUpdatedAt();

        sleep(1);

        $this->entityManager->flush();

        // different object, but values are the same
        $this->assertSame($entity->getServerCreatedAt(), $createdAt, 'Creation timestamp has changed');
        $this->assertSame($entity->getServerUpdatedAt(), $updatedAt, 'Update timestamp has changed');
    }
}
