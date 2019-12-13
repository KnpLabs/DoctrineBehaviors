<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\BlameableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UserEntity;

final class BlameableWithEntityTest extends AbstractBehaviorTestCase
{
    public function testUserEntity(): void
    {
        $entity = new BlameableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertInstanceOf(UserEntity::class, $entity->getCreatedBy());
        $this->assertInstanceOf(UserEntity::class, $entity->getUpdatedBy());
    }

    public function testSubscriberWithUserCallback(): void
    {
        $entity = new BlameableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $createdBy = $entity->getCreatedBy();

        $blameableRepository = $this->entityManager->getRepository(BlameableEntity::class);

        /** @var BlameableEntity $entity */
        $entity = $blameableRepository->find($id);
        $entity->setTitle('test');

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->assertInstanceOf(UserEntity::class, $entity->getCreatedBy());

        /** @var UserEntity $createdBy */
        $this->assertSame($createdBy->getUsername(), $entity->getCreatedBy()->getUsername());

        $this->assertSame($entity->getCreatedBy(), $entity->getUpdatedBy());
    }

    protected function provideCustomConfig(): ?string
    {
        return __DIR__ . '/../config/config_test_with_blameable_entity.yaml';
    }
}
