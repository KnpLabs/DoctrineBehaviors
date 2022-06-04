<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM\Translatable;

use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable\ExtendedTranslatableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable\ExtendedTranslatableEntityTranslation;

final class TranslatableInheritanceTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository<ExtendedTranslatableEntity>
     */
    private ObjectRepository $objectRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectRepository = $this->entityManager->getRepository(ExtendedTranslatableEntity::class);
    }

    public function testShouldPersistTranslationsWithInheritance(): void
    {
        $entity = new ExtendedTranslatableEntity();

        /** @var ExtendedTranslatableEntityTranslation $frenchEntity */
        $frenchEntity = $entity->translate('fr');
        $frenchEntity->setTitle('fabuleux');
        $frenchEntity->setExtendedTitle('fabuleux');

        /** @var ExtendedTranslatableEntityTranslation $englishEntity */
        $englishEntity = $entity->translate('en');
        $englishEntity->setTitle('awesome');
        $englishEntity->setExtendedTitle('awesome');

        /** @var ExtendedTranslatableEntityTranslation $russianEntity */
        $russianEntity = $entity->translate('ru');
        $russianEntity->setTitle('удивительный');
        $russianEntity->setExtendedTitle('удивительный');

        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();

        $this->entityManager->clear();

        /** @var ExtendedTranslatableEntity $entity */
        $entity = $this->objectRepository->find($id);

        /** @var ExtendedTranslatableEntityTranslation $frenchEntity */
        $frenchEntity = $entity->translate('fr');
        $this->assertSame('fabuleux', $frenchEntity->getTitle());
        $this->assertSame('fabuleux', $frenchEntity->getExtendedTitle());

        /** @var ExtendedTranslatableEntityTranslation $englishEntity */
        $englishEntity = $entity->translate('en');
        $this->assertSame('awesome', $englishEntity->getTitle());
        $this->assertSame('awesome', $englishEntity->getExtendedTitle());

        /** @var ExtendedTranslatableEntityTranslation $russianEntity */
        $russianEntity = $entity->translate('ru');
        $this->assertSame('удивительный', $russianEntity->getTitle());
        $this->assertSame('удивительный', $russianEntity->getExtendedTitle());
    }
}
