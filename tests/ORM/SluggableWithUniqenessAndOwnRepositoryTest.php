<?php


declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SluggableWithUniqenessAndOwnRepositoryEntity;

final class SluggableWithUniqenessAndOwnRepositoryTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository|EntityRepository
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->entityManager->getRepository(SluggableWithUniqenessAndOwnRepositoryEntity::class);
    }

    public function testSlugSameContextSameUnitOfWork(): void
    {
        $entity1 = new SluggableWithUniqenessAndOwnRepositoryEntity();
        $entity1->setName("Lorem ipsum");
        $entity1->setSlugContext(1);

        $entity2 = new SluggableWithUniqenessAndOwnRepositoryEntity();
        $entity2->setName("Lorem ipsum");
        $entity2->setSlugContext(1);

        $this->entityManager->persist($entity1);
        $this->entityManager->persist($entity2);
        $this->entityManager->flush();

        $id1 = $entity1->getId();
        $this->assertNotNull($id1);

        $id2 = $entity2->getId();
        $this->assertNotNull($id2);

        $this->entityManager->clear();

        $entity1   = $this->repository->find($id1);
        $entity2   = $this->repository->find($id2);

        $this->assertNotNull($entity1);
        $this->assertNotNull($entity2);

        $this->assertSame('Lorem ipsum', $entity1->getName());
        $this->assertSame('lorem-ipsum', $entity1->getSlug());
        $this->assertSame(1, $entity1->getSlugContext());
        $this->assertSame('Lorem ipsum', $entity2->getName());
        $this->assertSame('lorem-ipsum-1', $entity2->getSlug());
        $this->assertSame(1, $entity2->getSlugContext());
    }

    public function testSlugDifferentContextSameUnitOfWork(): void
    {
        $entity1 = new SluggableWithUniqenessAndOwnRepositoryEntity();
        $entity1->setName("Lorem ipsum");
        $entity1->setSlugContext(1);

        $entity2 = new SluggableWithUniqenessAndOwnRepositoryEntity();
        $entity2->setName("Lorem ipsum");
        $entity2->setSlugContext(2);

        $this->entityManager->persist($entity1);
        $this->entityManager->persist($entity2);
        $this->entityManager->flush();

        $id1 = $entity1->getId();
        $this->assertNotNull($id1);

        $id2 = $entity2->getId();
        $this->assertNotNull($id2);

        $this->entityManager->clear();

        $entity1   = $this->repository->find($id1);
        $entity2   = $this->repository->find($id2);

        $this->assertNotNull($entity1);
        $this->assertNotNull($entity2);

        $this->assertSame('Lorem ipsum', $entity1->getName());
        $this->assertSame('lorem-ipsum', $entity1->getSlug());
        $this->assertSame(1, $entity1->getSlugContext());
        $this->assertSame('Lorem ipsum', $entity2->getName());
        $this->assertSame('lorem-ipsum', $entity2->getSlug());
        $this->assertSame(2, $entity2->getSlugContext());
    }

    public function testSlugSameContextDifferentUnitOfWork(): void
    {
        $entity1 = new SluggableWithUniqenessAndOwnRepositoryEntity();
        $entity1->setName("Lorem ipsum");
        $entity1->setSlugContext(1);
        $this->entityManager->persist($entity1);
        $this->entityManager->flush();

        $entity2 = new SluggableWithUniqenessAndOwnRepositoryEntity();
        $entity2->setName("Lorem ipsum");
        $entity2->setSlugContext(1);

        $this->entityManager->persist($entity2);
        $this->entityManager->flush();

        $id1 = $entity1->getId();
        $this->assertNotNull($id1);

        $id2 = $entity2->getId();
        $this->assertNotNull($id2);

        $this->entityManager->clear();

        $entity1   = $this->repository->find($id1);
        $entity2   = $this->repository->find($id2);

        $this->assertNotNull($entity1);
        $this->assertNotNull($entity2);

        $this->assertSame('Lorem ipsum', $entity1->getName());
        $this->assertSame('lorem-ipsum', $entity1->getSlug());
        $this->assertSame(1, $entity1->getSlugContext());
        $this->assertSame('Lorem ipsum', $entity2->getName());
        $this->assertSame('lorem-ipsum-1', $entity2->getSlug());
        $this->assertSame(1, $entity2->getSlugContext());
    }

    public function testSlugDifferentContextDifferentUnitOfWork(): void
    {
        $entity1 = new SluggableWithUniqenessAndOwnRepositoryEntity();
        $entity1->setName("Lorem ipsum");
        $entity1->setSlugContext(1);
        $this->entityManager->persist($entity1);
        $this->entityManager->flush();

        $entity2 = new SluggableWithUniqenessAndOwnRepositoryEntity();
        $entity2->setName("Lorem ipsum");
        $entity2->setSlugContext(2);

        $this->entityManager->persist($entity2);
        $this->entityManager->flush();

        $id1 = $entity1->getId();
        $this->assertNotNull($id1);

        $id2 = $entity2->getId();
        $this->assertNotNull($id2);

        $this->entityManager->clear();

        $entity1   = $this->repository->find($id1);
        $entity2   = $this->repository->find($id2);

        $this->assertNotNull($entity1);
        $this->assertNotNull($entity2);

        $this->assertSame('Lorem ipsum', $entity1->getName());
        $this->assertSame('lorem-ipsum', $entity1->getSlug());
        $this->assertSame(1, $entity1->getSlugContext());
        $this->assertSame('Lorem ipsum', $entity2->getName());
        $this->assertSame('lorem-ipsum', $entity2->getSlug());
        $this->assertSame(2, $entity2->getSlugContext());
    }
}
