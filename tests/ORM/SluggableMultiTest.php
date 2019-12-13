<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use DateTime;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
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
        $entity = new SluggableMultiEntity();
        $entity->setName('The name');

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $entity->setDate(new DateTime());

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertSame('the+name+title', $entity->getSlug());
    }

    public function testUpdatedSlug(): void
    {
        $entity = new SluggableMultiEntity();
        $entity->setName('The name');

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertSame('the+name+title', $entity->getSlug());
        $entity->setName('The name 2');

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertSame('the+name+2+title', $entity->getSlug());
    }
}
