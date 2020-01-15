<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SluggableTranslatableEntity;

final class SluggableWithTranslatableEntityAndUniquenessTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository|EntityRepository
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
        $entity->translate('fr')->setTitle('Lorem ipsum');
        $entity->translate('en')->setTitle('Lorem ipsum');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->assertNotNull($id);

        $this->entityManager->clear();

        /** @var SluggableTranslatableEntity $entity */
        $entity = $this->translatableRepository->find($id);
        $entityFR = $entity->translate('fr');
        $entityEN = $entity->translate('en');

        $this->assertNotNull($entity);
        $this->assertSame('Lorem ipsum', $entityFR->getTitle());
        $this->assertSame('lorem-ipsum', $entityFR->getSlug());
        $this->assertSame('Lorem ipsum', $entityEN->getTitle());
        $this->assertSame('lorem-ipsum-1', $entityEN->getSlug());
    }

    public function testNotUpdatedSlug(): void
    {
        $entity = new SluggableTranslatableEntity();
        $entity->translate('fr')->setTitle('Lorem ipsum');
        $entity->translate('en')->setTitle('Lorem ipsum');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $entityFR = $entity->translate('fr');
        $entityEN = $entity->translate('en');

        $this->assertSame('lorem-ipsum', $entityFR->getSlug());
        $this->assertSame('lorem-ipsum-1', $entityEN->getSlug());
        $entity->translate('fr')->setTitle('Mon titre');
        $entity->translate('en')->setTitle('My title');

        $this->assertSame('lorem-ipsum', $entityFR->getSlug());
        $this->assertSame('lorem-ipsum-1', $entityEN->getSlug());
    }
}
