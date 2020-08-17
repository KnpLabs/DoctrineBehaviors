<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\BlameableEntityWithUserEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UserEntity;

final class BlameableWithEntityTest extends AbstractBehaviorTestCase
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var ObjectRepository|EntityRepository
     */
    private $blameableRepository;

    /**
     * @var UserEntity
     */
    private $userEntity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userProvider = static::$container->get(UserProviderInterface::class);
        $this->blameableRepository = $this->entityManager->getRepository(BlameableEntityWithUserEntity::class);
        $this->userEntity = $this->userProvider->provideUser();
    }

    public function testCreate(): void
    {
        $blameableEntityWithUserEntity = new BlameableEntityWithUserEntity();

        $this->entityManager->persist($blameableEntityWithUserEntity);
        $this->entityManager->flush();

        $this->assertInstanceOf(UserEntity::class, $blameableEntityWithUserEntity->getCreatedBy());
        $this->assertInstanceOf(UserEntity::class, $blameableEntityWithUserEntity->getUpdatedBy());
        $this->assertSame($this->userEntity, $blameableEntityWithUserEntity->getCreatedBy());
        $this->assertSame($this->userEntity, $blameableEntityWithUserEntity->getUpdatedBy());
        $this->assertNull($blameableEntityWithUserEntity->getDeletedBy());
    }

    public function testUpdate(): void
    {
        $entity = new BlameableEntityWithUserEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $createdBy = $entity->getCreatedBy();

        $this->userProvider->changeUser('user2');

        /** @var BlameableEntityWithUserEntity $entity */
        $entity = $this->blameableRepository->find($id);

        $this->enableDebugStackLogger();

        $entity->setTitle('test');
        $this->entityManager->flush();

        $this->assertCount(3, $this->debugStack->queries);
        $this->assertSame('"START TRANSACTION"', $this->debugStack->queries[1]['sql']);
        $this->assertSame(
            'UPDATE BlameableEntityWithUserEntity SET title = ?, updatedBy_id = ? WHERE id = ?',
            $this->debugStack->queries[2]['sql']
        );
        $this->assertSame('"COMMIT"', $this->debugStack->queries[3]['sql']);

        $this->assertInstanceOf(UserEntity::class, $entity->getCreatedBy());
        $this->assertInstanceOf(UserEntity::class, $entity->getUpdatedBy());

        $user2 = $this->userProvider->provideUser();

        /** @var UserEntity $createdBy */
        $this->assertSame($createdBy, $entity->getCreatedBy(), 'createdBy is constant');
        $this->assertSame($user2, $entity->getUpdatedBy());

        $this->assertNotSame(
            $entity->getCreatedBy(),
            $entity->getUpdatedBy(),
            'createBy and updatedBy have diverged since new update'
        );
    }

    protected function provideCustomConfig(): ?string
    {
        return __DIR__ . '/../config/config_test_with_blameable_entity.yaml';
    }
}
