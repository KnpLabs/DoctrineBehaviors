<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable\V1\AbstractV1TranslatableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable\V1\AbstractV1TranslatableEntityTranslation;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable\V1\ExtendedV1TranslatableEntity;

final class TranslatableInheritanceV1Test extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository
     */
    private $objectRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectRepository = $this->entityManager->getRepository(ExtendedV1TranslatableEntity::class);
    }

    public function testGetTranslationEntityClass(): void
    {
        $this->assertSame(AbstractV1TranslatableEntityTranslation::class, AbstractV1TranslatableEntity::getTranslationEntityClass());
        $this->assertSame(AbstractV1TranslatableEntityTranslation::class, ExtendedV1TranslatableEntity::getTranslationEntityClass());
    }

    public function testShouldPersistTranslationsWithInheritance(): void
    {
        $entity = new ExtendedV1TranslatableEntity();
        $entity->translate('fr')->setTitle('fabuleux');

        $entity->translate('en')->setTitle('awesome');

        $entity->translate('ru')->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();

        $this->entityManager->clear();

        /** @var TranslatableInterface $entity */
        $entity = $this->objectRepository->find($id);

        $this->assertSame('fabuleux', $entity->translate('fr')->getTitle());

        $this->assertSame('awesome', $entity->translate('en')->getTitle());

        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());
    }
}
