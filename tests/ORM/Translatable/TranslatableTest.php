<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM\Translatable;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Contract\Translatable\TranslatableEntityWithCustomInterface;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TranslatableCustomIdentifierEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TranslatableCustomizedEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TranslatableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TranslatableEntityTranslation;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translation\TranslatableCustomizedEntityTranslation;

final class TranslatableTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository<TranslatableEntity>
     */
    private ObjectRepository $translatableRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translatableRepository = $this->entityManager->getRepository(TranslatableEntity::class);
    }

    public function testShouldPersistTranslations(): void
    {
        $translatableEntity = new TranslatableEntity();
        $translatableEntity->translate('fr')
            ->setTitle('fabuleux');
        $translatableEntity->translate('en')
            ->setTitle('awesome');
        $translatableEntity->translate('ru')
            ->setTitle('удивительный');
        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);
        $this->entityManager->flush();

        $id = $translatableEntity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $translatableEntity */
        $translatableEntity = $this->translatableRepository->find($id);

        $this->assertSame('fabuleux', $translatableEntity->translate('fr')->getTitle());
        $this->assertSame('awesome', $translatableEntity->translate('en')->getTitle());
        $this->assertSame('удивительный', $translatableEntity->translate('ru')->getTitle());
    }

    public function testShouldPersistWithCustomIdentifier(): void
    {
        $translatableEntity = new TranslatableCustomIdentifierEntity();
        $translatableEntity->translate('en')
            ->setTitle('awesome');
        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);
        $this->entityManager->flush();

        $idColumn = $translatableEntity->getIdColumn();
        $this->entityManager->clear();

        /** @var TranslatableEntity $translatableEntity */
        $translatableEntity = $this->entityManager->getRepository(TranslatableCustomIdentifierEntity::class)->find(
            $idColumn
        );

        $this->assertSame('awesome', $translatableEntity->translate('en')->getTitle());
    }

    public function testShouldFallbackCountryLocaleToLanguageOnlyTranslation(): void
    {
        $translatableEntity = new TranslatableEntity();
        $translatableEntity->translate('en', false)
            ->setTitle('plastic bag');
        $translatableEntity->translate('fr', false)
            ->setTitle('sac plastique');
        $translatableEntity->translate('fr_CH', false)
            ->setTitle('cornet');
        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);
        $this->entityManager->flush();

        $id = $translatableEntity->getId();
        $this->entityManager->clear();

        $entity = $this->translatableRepository->find($id);
        $this->assertInstanceOf(TranslatableEntity::class, $entity);

        /** @var TranslatableEntity $entity */
        $this->assertSame('plastic bag', $entity->translate('de')->getTitle());
        $this->assertSame('sac plastique', $entity->translate('fr_FR')->getTitle());
        $this->assertSame('cornet', $entity->translate('fr_CH')->getTitle());
    }

    public function testShouldFallbackToDefaultLocaleIfNoCountryLocaleTranslation(): void
    {
        $translatableEntity = new TranslatableEntity();
        $translatableEntity->translate('en', false)
            ->setTitle('plastic bag');
        $translatableEntity->translate('fr_CH', false)
            ->setTitle('cornet');
        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);
        $this->entityManager->flush();

        $id = $translatableEntity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $translatableEntity */
        $translatableEntity = $this->translatableRepository->find($id);

        $this->assertSame('plastic bag', $translatableEntity->translate('de')->getTitle());
        $this->assertSame('plastic bag', $translatableEntity->translate('fr_FR')->getTitle());
        $this->assertSame('cornet', $translatableEntity->translate('fr_CH')->getTitle());
    }

    public function testShouldUpdateAndAddNewTranslations(): void
    {
        $translatableEntity = new TranslatableEntity();
        $translatableEntity->translate('en')
            ->setTitle('awesome');
        $translatableEntity->translate('ru')
            ->setTitle('удивительный');
        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);
        $this->entityManager->flush();

        $id = $translatableEntity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $translatableEntity */
        $translatableEntity = $this->translatableRepository->find($id);

        $this->assertSame('awesome', $translatableEntity->translate('en')->getTitle());
        $this->assertSame('удивительный', $translatableEntity->translate('ru')->getTitle());

        $translatableEntity->translate('en')
            ->setTitle('great');
        $translatableEntity->translate('fr', false)
            ->setTitle('fabuleux');
        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var TranslatableEntity $translatableEntity */
        $translatableEntity = $this->translatableRepository->find($id);

        $this->assertSame('great', $translatableEntity->translate('en')->getTitle());
        $this->assertSame('fabuleux', $translatableEntity->translate('fr')->getTitle());
        $this->assertSame('удивительный', $translatableEntity->translate('ru')->getTitle());
    }

    public function testTranslateMethodShouldAlwaysReturnTranslationObject(): void
    {
        $translatableEntity = new TranslatableEntity();

        $this->assertInstanceOf(TranslatableEntityTranslation::class, $translatableEntity->translate('fr'));
    }

    public function testSubscriberShouldConfigureEntityWithCurrentLocale(): void
    {
        $translatableEntity = new TranslatableEntity();
        // magic method
        $translatableEntity->setTitle('test');

        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);

        $this->entityManager->flush();

        $id = $translatableEntity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $translatableEntity */
        $translatableEntity = $this->translatableRepository->find($id);

        $this->assertSame('en', $translatableEntity->getCurrentLocale());
        $this->assertSame('test', $translatableEntity->getTitle());
        $this->assertSame('test', $translatableEntity->translate($translatableEntity->getCurrentLocale())->getTitle());
    }

    public function testSubscriberShouldConfigureEntityWithDefaultLocale(): void
    {
        $translatableEntity = new TranslatableEntity();
        // magic method
        $translatableEntity->setTitle('test');
        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);

        $this->entityManager->flush();

        $id = $translatableEntity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $translatableEntity */
        $translatableEntity = $this->translatableRepository->find($id);

        $this->assertSame('en', $translatableEntity->getDefaultLocale());
        // magic method
        $this->assertSame('test', $translatableEntity->getTitle());

        $this->assertSame('test', $translatableEntity->translate($translatableEntity->getDefaultLocale())->getTitle());
        $this->assertSame('test', $translatableEntity->translate('fr')->getTitle());
    }

    public function testShouldHaveOneToManyRelation(): void
    {
        $this->assertTranslationsOneToManyMapped(TranslatableEntity::class, TranslatableEntityTranslation::class);
    }

    public function testShouldHaveOneToManyRelationWhenTranslationClassNameIsCustom(): void
    {
        $this->assertTranslationsOneToManyMapped(
            TranslatableCustomizedEntity::class,
            TranslatableCustomizedEntityTranslation::class
        );
    }

    public function testShouldCreateOnlyOneTimeTheSameTranslation(): void
    {
        $translatableEntity = new TranslatableEntity();
        $translatableEntityTranslation = $translatableEntity->translate('fr');
        $translatableEntityTranslation->setTitle('fabuleux');
        $translatableEntity->translate('fr')
            ->setTitle('fabuleux2');
        $translatableEntity->translate('fr')
            ->setTitle('fabuleux3');

        $this->assertSame('fabuleux3', $translatableEntity->translate('fr')->getTitle());

        $givenObjectHash = spl_object_hash($translatableEntity->translate('fr'));
        $translationObjectHash = spl_object_hash($translatableEntityTranslation);
        $this->assertSame($givenObjectHash, $translationObjectHash);
    }

    public function testShouldRemoveTranslation(): void
    {
        $translatableEntity = new TranslatableEntity();
        $translatableEntity->translate('en')
            ->setTitle('Hello');
        $translatableEntity->translate('nl')
            ->setTitle('Hallo');
        $translatableEntity->mergeNewTranslations();
        $this->entityManager->persist($translatableEntity);
        $this->entityManager->flush();

        $translatableEntityTranslation = $translatableEntity->translate('nl');
        $translatableEntity->removeTranslation($translatableEntityTranslation);
        $this->entityManager->flush();

        $this->entityManager->refresh($translatableEntity);
        $this->assertNotSame('Hallo', $translatableEntity->translate('nl')->getTitle());
    }

    public function testSetTranslations(): void
    {
        $translatableEntity = new TranslatableEntity();
        $translatableEntityTranslation = $translatableEntity->translate('en');

        $translatableEntity->setTranslations([$translatableEntityTranslation]);

        $this->assertCount(1, $translatableEntity->getTranslations());
    }

    public function testShouldNotPersistNewEmptyTranslations(): void
    {
        $translatableEntity = new TranslatableEntity();
        $translatableEntity->translate('fr')
            ->setTitle('fabuleux');
        $translatableEntity->translate('en')
            ->setTitle('');
        $translatableEntity->translate('ru')
            ->setTitle('удивительный');

        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);
        $this->entityManager->flush();

        $id = $translatableEntity->getId();
        $this->entityManager->clear();

        $entity = $this->translatableRepository->find($id);
        $this->assertIsObject($entity);
        $this->assertInstanceOf(TranslatableEntity::class, $entity);

        /** @var TranslatableEntity $entity */
        $this->assertSame('fabuleux', $entity->translate('fr')->getTitle());

        // empty English translation
        $this->assertNull($entity->translate('en')->getTitle());

        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());
    }

    public function testShouldRemoveTranslationsWhichBecomeEmpty(): void
    {
        $translatableEntity = new TranslatableEntity();
        $translatableEntity->translate('fr')
            ->setTitle('fabuleux');
        $translatableEntity->translate('en')
            ->setTitle('awesome');
        $translatableEntity->translate('ru')
            ->setTitle('удивительный');

        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);
        $this->entityManager->flush();

        $translatableEntity->translate('en')
            ->setTitle('');
        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);
        $this->entityManager->flush();

        $id = $translatableEntity->getId();
        $this->entityManager->clear();

        $translatableEntity = $this->translatableRepository->find($id);

        $this->assertIsObject($translatableEntity);
        $this->assertInstanceOf(TranslatableEntity::class, $translatableEntity);

        /** @var TranslatableEntity $translatableEntity */
        $this->assertSame('fabuleux', $translatableEntity->translate('fr')->getTitle());
        $this->assertNull($translatableEntity->translate('en')->getTitle());
        $this->assertSame('удивительный', $translatableEntity->translate('ru')->getTitle());
    }

    public function testPhpStanExtensionOnInterfaces(): void
    {
        /** @var TranslationInterface $translatableEntityTranslation */
        $translatableEntityTranslation = new TranslatableEntityTranslation();
        $translatableEntityTranslation->setLocale('fr');

        /** @var TranslatableInterface $translatableEntity */
        $translatableEntity = new TranslatableEntity();
        $translatableEntity->addTranslation($translatableEntityTranslation);

        $this->assertSame($translatableEntity, $translatableEntityTranslation->getTranslatable());
        $this->assertSame($translatableEntityTranslation, $translatableEntity->getTranslations()->get('fr'));
    }

    public function testTranslationIsNotEmptyWithZeroAsValue(): void
    {
        $translatableEntity = new TranslatableEntity();
        $translatableEntity->translate('fr')
            ->setTitle('0');
        $translatableEntity->translate('en')
            ->setTitle('0');

        $translatableEntity->mergeNewTranslations();

        $this->entityManager->persist($translatableEntity);
        $this->entityManager->flush();

        $id = $translatableEntity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $translatableEntity */
        $translatableEntity = $this->translatableRepository->find($id);

        $this->assertFalse($translatableEntity->translate('fr')->isEmpty());
        $this->assertFalse($translatableEntity->translate('en')->isEmpty());
        $this->assertSame('0', $translatableEntity->translate('fr')->getTitle());
        $this->assertSame('0', $translatableEntity->translate('en')->getTitle());
    }

    public function testCustomInterface(): void
    {
        $translatableEntityWithCustom = new TranslatableEntityWithCustomInterface();
        $translatableEntityWithCustom->translate('en')
            ->setTitle('awesome');
        $translatableEntityWithCustom->mergeNewTranslations();

        $this->assertSame('awesome', $translatableEntityWithCustom->translate('en')->getTitle());
    }

    /**
     * @param class-string $translatableClass
     * @param class-string $translationClass
     * Asserts that the one to many relationship between translatable and translations is mapped correctly.
     */
    private function assertTranslationsOneToManyMapped(string $translatableClass, string $translationClass): void
    {
        $translationClassMetadata = $this->entityManager->getClassMetadata($translationClass);
        $this->assertSame($translatableClass, $translationClassMetadata->getAssociationTargetClass('translatable'));

        $translatableClassMetadata = $this->entityManager->getClassMetadata($translatableClass);
        $this->assertSame($translationClass, $translatableClassMetadata->getAssociationTargetClass('translations'));

        $this->assertTrue($translatableClassMetadata->isAssociationInverseSide('translations'));

        $this->assertSame(
            ClassMetadata::ONE_TO_MANY,
            $translatableClassMetadata->getAssociationMapping('translations')['type']
        );
    }
}
