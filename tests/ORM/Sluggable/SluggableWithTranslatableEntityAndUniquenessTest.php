<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM\Sluggable;

use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Sluggable\SluggableTranslatableEntity;

final class SluggableWithTranslatableEntityAndUniquenessTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository<SluggableTranslatableEntity>
     */
    private $translatableRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translatableRepository = $this->entityManager->getRepository(SluggableTranslatableEntity::class);
    }

    public function testSlugLoading(): void
    {
        $entity = new SluggableTranslatableEntity();
        $entity->translate('fr')
            ->setTitle('Lorem ipsum');
        $entity->translate('en')
            ->setTitle('Lorem ipsum');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->assertNotNull($id);

        $this->entityManager->clear();

        /** @var \Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Sluggable\SluggableTranslatableEntity $entity */
        $entity = $this->translatableRepository->find($id);
        $sluggableTranslatableEntityTranslation = $entity->translate('fr');
        $entityEN = $entity->translate('en');

        $this->assertNotNull($entity);
        $this->assertSame('Lorem ipsum', $sluggableTranslatableEntityTranslation->getTitle());
        $this->assertSame('lorem-ipsum', $sluggableTranslatableEntityTranslation->getSlug());
        $this->assertSame('Lorem ipsum', $entityEN->getTitle());
        $this->assertSame('lorem-ipsum-1', $entityEN->getSlug());
    }

    public function testNotUpdatedSlug(): void
    {
        $sluggableTranslatableEntity = new SluggableTranslatableEntity();
        $sluggableTranslatableEntity->translate('fr')
            ->setTitle('Lorem ipsum');
        $sluggableTranslatableEntity->translate('en')
            ->setTitle('Lorem ipsum');
        $sluggableTranslatableEntity->mergeNewTranslations();

        $this->entityManager->persist($sluggableTranslatableEntity);
        $this->entityManager->flush();

        $sluggableTranslatableEntityTranslation = $sluggableTranslatableEntity->translate('fr');
        $entityEN = $sluggableTranslatableEntity->translate('en');

        $this->assertSame('lorem-ipsum', $sluggableTranslatableEntityTranslation->getSlug());
        $this->assertSame('lorem-ipsum-1', $entityEN->getSlug());
        $sluggableTranslatableEntity->translate('fr')
            ->setTitle('Mon titre');
        $sluggableTranslatableEntity->translate('en')
            ->setTitle('My title');

        $this->assertSame('lorem-ipsum', $sluggableTranslatableEntityTranslation->getSlug());
        $this->assertSame('lorem-ipsum-1', $entityEN->getSlug());
    }
}
