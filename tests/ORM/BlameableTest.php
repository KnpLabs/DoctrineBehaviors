<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\BlameableEntity;

final class BlameableTest extends AbstractBehaviorTestCase
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var ObjectRepository|EntityRepository
     */
    private $blameableRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userProvider = static::$container->get(UserProviderInterface::class);
        $this->blameableRepository = $this->entityManager->getRepository(BlameableEntity::class);
    }

    public function testCreate(): void
    {
        $blameableEntity = new BlameableEntity();

        $this->entityManager->persist($blameableEntity);
        $this->entityManager->flush();

        $this->assertSame('user', $blameableEntity->getCreatedBy());
        $this->assertSame('user', $blameableEntity->getUpdatedBy());
        $this->assertNull($blameableEntity->getDeletedBy());
    }

    public function testUpdate(): void
    {
        $entity = new BlameableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $createdBy = $entity->getCreatedBy();
        $this->entityManager->clear();

        $this->userProvider->changeUser('user2');

        /** @var BlameableEntity $entity */
        $entity = $this->blameableRepository->find($id);

        $this->enableDebugStackLogger();

        // need to modify at least one column to trigger onUpdate
        $entity->setTitle('test');
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->assertCount(3, $this->debugStack->queries);
        $this->assertSame('"START TRANSACTION"', $this->debugStack->queries[1]['sql']);
        $this->assertSame(
            'UPDATE BlameableEntity SET title = ?, updatedBy = ? WHERE id = ?',
            $this->debugStack->queries[2]['sql']
        );
        $this->assertSame('"COMMIT"', $this->debugStack->queries[3]['sql']);

        /** @var BlameableEntity $entity */
        $entity = $this->blameableRepository->find($id);

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
        $entity = new BlameableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->entityManager->clear();

        $this->userProvider->changeUser('user3');

        /** @var BlameableEntity $entity */
        $entity = $this->blameableRepository->find($id);

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->assertSame('user3', $entity->getDeletedBy());
    }

    public function testExtraSqlCalls(): void
    {
        $blameableEntity = new BlameableEntity();

        $this->enableDebugStackLogger();

        $this->entityManager->persist($blameableEntity);
        $this->entityManager->flush();

        $expectedCount = $this->isPostgreSql() ? 4 : 3;
        $startKey = $this->isPostgreSql() ? 2 : 1;

        $this->assertCount($expectedCount, $this->debugStack->queries);
        $this->assertSame('"START TRANSACTION"', $this->debugStack->queries[$startKey]['sql']);

        $sql2 = $this->debugStack->queries[$startKey + 1]['sql'];
        if ($this->isPostgreSql()) {
            $this->assertSame(
                'INSERT INTO BlameableEntity (id, title, createdBy, updatedBy, deletedBy) VALUES (?, ?, ?, ?, ?)',
                $sql2
            );
        } else {
            $this->assertSame(
                'INSERT INTO BlameableEntity (title, createdBy, updatedBy, deletedBy) VALUES (?, ?, ?, ?)',
                $sql2
            );
        }

        $this->assertSame('"COMMIT"', $this->debugStack->queries[$startKey + 2]['sql']);
    }
}
