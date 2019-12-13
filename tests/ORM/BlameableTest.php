<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
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
        $entity = new BlameableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertSame('user', $entity->getCreatedBy());
        $this->assertSame('user', $entity->getUpdatedBy());
        $this->assertNull($entity->getDeletedBy());
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

        $entity->setTitle('test'); // need to modify at least one column to trigger onUpdate
        $this->entityManager->flush();
        $this->entityManager->clear();

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
}
