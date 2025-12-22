<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM\Blameable;

use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Blameable\BlameableEntityWithUserEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UserEntity;

final class BlameableWithEntityTest extends AbstractBehaviorTestCase
{
    private UserProviderInterface $userProvider;

    /**
     * @var ObjectRepository<BlameableEntityWithUserEntity>
     */
    private ObjectRepository $blameableRepository;

    private UserEntity $userEntity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userProvider = $this->getService(UserProviderInterface::class);
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

        $debugStack = $this->getAndResetDebugStack();

        $entity->setTitle('test');
        $this->entityManager->flush();

        $this->assertCount(3, $debugStack->queries);
        $this->assertSame('"START TRANSACTION"', $debugStack->queries[1]['sql']);
        $this->assertSame(
            'UPDATE BlameableEntityWithUserEntity SET title = ?, updatedBy_id = ? WHERE id = ?',
            $debugStack->queries[2]['sql']
        );
        $this->assertSame('"COMMIT"', $debugStack->queries[3]['sql']);

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

    /**
     * @return string[]
     */
    protected function provideCustomConfigs(): array
    {
        return [__DIR__ . '/../../config/config_test_with_blameable_entity.php'];
    }
}
