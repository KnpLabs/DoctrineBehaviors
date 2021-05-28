<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Knp\DoctrineBehaviors\Contract\Entity\UlidableInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UlidableEntity;

final class UlidableTest extends AbstractBehaviorTestCase
{
    public function testUlidLoading(): void
    {
        $entity = new UlidableEntity();
        $entity->setName('The name');

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->assertNotNull($id);

        $this->entityManager->clear();

        $ulidableRepository = $this->entityManager->getRepository(UlidableEntity::class);

        /** @var UlidableInterface $entity */
        $entity = $ulidableRepository->find($id);

        $this->assertNotNull($entity);
    }
}
