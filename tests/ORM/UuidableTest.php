<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Knp\DoctrineBehaviors\Contract\Entity\UuidableInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UuidableEntity;
use Ramsey\Uuid\UuidInterface;

final class UuidableTest extends AbstractBehaviorTestCase
{
    public function testUuidLoading(): void
    {
        $entity = new UuidableEntity();
        $entity->setName('The name');

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->assertNotNull($id);

        $this->entityManager->clear();

        $uuidableRepository = $this->entityManager->getRepository(UuidableEntity::class);

        /** @var UuidableInterface $entity */
        $entity = $uuidableRepository->find($id);

        $this->assertNotNull($entity);
        $this->assertInstanceOf(UuidInterface::class, $entity->getUuid());
    }
}
