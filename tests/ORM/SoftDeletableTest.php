<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use DateTime;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\SoftDeletableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\SoftDeletableEntityInherit;

final class SoftDeletableTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository|EntityRepository
     */
    private $softDeletableRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->softDeletableRepository = $this->entityManager->getRepository(SoftDeletableEntity::class);
    }

    public function testDelete(): void
    {
        $entity = new SoftDeletableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertNotNull($id = $entity->getId());
        $this->assertFalse($entity->isDeleted());

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var SoftDeletableEntity $entity */
        $entity = $this->softDeletableRepository->find($id);

        $this->assertNotNull($entity);
        $this->assertTrue($entity->isDeleted());
    }

    public function testPostDelete(): void
    {
        $entity = new SoftDeletableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertNotNull($id = $entity->getId());

        $entity->setDeletedAt((new DateTime())->modify('+1 day'));

        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var SoftDeletableEntity $entity */
        $entity = $this->softDeletableRepository->find($id);

        $this->assertNotNull($entity);
        $this->assertFalse($entity->isDeleted());
        $this->assertTrue($entity->willBeDeleted());
        $this->assertTrue($entity->willBeDeleted((new DateTime())->modify('+2 day')));
        $this->assertFalse($entity->willBeDeleted((new DateTime())->modify('+12 hour')));

        $entity->setDeletedAt((new DateTime())->modify('-1 day'));

        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var SoftDeletableEntity $entity */
        $entity = $this->softDeletableRepository->find($id);

        $this->assertNotNull($entity);
        $this->assertTrue($entity->isDeleted());
    }

    public function testDeleteInheritance(): void
    {
        $entity = new SoftDeletableEntityInherit();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $this->assertTrue($entity->isDeleted());
    }

    public function testRestore(): void
    {
        $entity = new SoftDeletableEntityInherit();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        $this->assertTrue($entity->isDeleted());

        $entity->restore();

        $this->assertFalse($entity->isDeleted());
    }
}
