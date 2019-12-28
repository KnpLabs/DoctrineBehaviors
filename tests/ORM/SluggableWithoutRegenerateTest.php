<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SluggableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SluggableWithoutRegenerateEntity;

final class SluggableWithoutRegenerateTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository|EntityRepository
     */
    private $sluggableWithoutRegenerateRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sluggableWithoutRegenerateRepository = $this->entityManager->getRepository(
            SluggableWithoutRegenerateEntity::class
        );
    }

    public function testSlugLoading(): void
    {
        $entity = new SluggableWithoutRegenerateEntity();
        $entity->setName('The name');

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->assertNotNull($id);

        $this->entityManager->clear();

        /** @var SluggableEntity $entity */
        $entity = $this->sluggableWithoutRegenerateRepository->find($id);

        $this->assertNotNull($entity);
        $this->assertSame('the-name', $entity->getSlug());
    }

    public function testNotUpdatedSlug(): void
    {
        $entity = new SluggableWithoutRegenerateEntity();
        $entity->setName('The name');

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertSame('the-name', $entity->getSlug());

        $entity->setName('The name 2');

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $this->assertSame('the-name', $entity->getSlug());
    }
}
