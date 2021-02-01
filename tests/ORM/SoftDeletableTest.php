<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SoftDeletableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SoftDeletableEntityInherit;

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
        $softDeletableEntityInherit = new SoftDeletableEntityInherit();

        $this->entityManager->persist($softDeletableEntityInherit);
        $this->entityManager->flush();

        $this->entityManager->remove($softDeletableEntityInherit);
        $this->entityManager->flush();

        $this->assertTrue($softDeletableEntityInherit->isDeleted());
    }

    public function testRestore(): void
    {
        $softDeletableEntityInherit = new SoftDeletableEntityInherit();

        $this->entityManager->persist($softDeletableEntityInherit);
        $this->entityManager->flush();

        $this->entityManager->remove($softDeletableEntityInherit);
        $this->entityManager->flush();

        $this->assertTrue($softDeletableEntityInherit->isDeleted());

        $softDeletableEntityInherit->restore();

        $this->assertFalse($softDeletableEntityInherit->isDeleted());
    }

    public function testExtraSqlCalls(): void
    {
        $softDeletableEntity = new SoftDeletableEntity();
        $this->entityManager->persist($softDeletableEntity);
        $this->entityManager->flush();

        $id = $softDeletableEntity->getId();
        $this->assertNotNull($id);
        $this->assertFalse($softDeletableEntity->isDeleted());

        $this->enableDebugStackLogger();

        $this->entityManager->remove($softDeletableEntity);
        $this->entityManager->flush();

        $this->assertCount(3, $this->debugStack->queries);
        $this->assertSame('"START TRANSACTION"', $this->debugStack->queries[1]['sql']);
        $this->assertSame(
            'UPDATE SoftDeletableEntity SET deletedAt = ? WHERE id = ?',
            $this->debugStack->queries[2]['sql']
        );
        $this->assertSame('"COMMIT"', $this->debugStack->queries[3]['sql']);
    }
}
