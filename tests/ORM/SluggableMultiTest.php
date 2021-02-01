<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SluggableMultiEntity;

final class SluggableMultiTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository|EntityRepository
     */
    private $sluggableRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sluggableRepository = $this->entityManager->getRepository(SluggableMultiEntity::class);
    }

    public function testSlugLoading(): void
    {
        $entity = new SluggableMultiEntity();
        $entity->setName('The name');

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertNotNull($id = $entity->getId());

        $this->entityManager->clear();

        /** @var SluggableMultiEntity $entity */
        $entity = $this->sluggableRepository->find($id);

        $this->assertNotNull($entity);
        $this->assertSame('the+name+title', $entity->getSlug());
    }

    public function testNotUpdatedSlug(): void
    {
        $sluggableMultiEntity = new SluggableMultiEntity();
        $sluggableMultiEntity->setName('The name');

        $this->entityManager->persist($sluggableMultiEntity);
        $this->entityManager->flush();

        $sluggableMultiEntity->setDate(new DateTime());

        $this->entityManager->persist($sluggableMultiEntity);
        $this->entityManager->flush();

        $this->assertSame('the+name+title', $sluggableMultiEntity->getSlug());
    }

    public function testUpdatedSlug(): void
    {
        $sluggableMultiEntity = new SluggableMultiEntity();
        $sluggableMultiEntity->setName('The name');

        $this->entityManager->persist($sluggableMultiEntity);
        $this->entityManager->flush();

        $this->assertSame('the+name+title', $sluggableMultiEntity->getSlug());
        $sluggableMultiEntity->setName('The name 2');

        $this->entityManager->persist($sluggableMultiEntity);
        $this->entityManager->flush();

        $this->assertSame('the+name+2+title', $sluggableMultiEntity->getSlug());
    }
}
