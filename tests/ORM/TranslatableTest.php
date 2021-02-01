<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ObjectRepository;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TranslatableCustomizedEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TranslatableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TranslatableEntityTranslation;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translation\TranslatableCustomizedEntityTranslation;

final class TranslatableTest extends AbstractBehaviorTestCase
{
    /**
     * @var ObjectRepository|EntityRepository
     */
    private $translatableRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translatableRepository = $this->entityManager->getRepository(TranslatableEntity::class);
    }

    public function testShouldPersistTranslations(): void
    {
        $entity = new TranslatableEntity();
        $entity->translate('fr')
            ->setTitle('fabuleux');
        $entity->translate('en')
            ->setTitle('awesome');
        $entity->translate('ru')
            ->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $entity */
        $entity = $this->translatableRepository->find($id);

        $this->assertSame('fabuleux', $entity->translate('fr')->getTitle());
        $this->assertSame('awesome', $entity->translate('en')->getTitle());
        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());
    }

    public function testShouldFallbackCountryLocaleToLanguageOnlyTranslation(): void
    {
        $entity = new TranslatableEntity();
        $entity->translate('en', false)
            ->setTitle('plastic bag');
        $entity->translate('fr', false)
            ->setTitle('sac plastique');
        $entity->translate('fr_CH', false)
            ->setTitle('cornet');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $entity */
        $entity = $this->translatableRepository->find($id);

        $this->assertSame('plastic bag', $entity->translate('de')->getTitle());
        $this->assertSame('sac plastique', $entity->translate('fr_FR')->getTitle());
        $this->assertSame('cornet', $entity->translate('fr_CH')->getTitle());
    }

    public function testShouldUpdateAndAddNewTranslations(): void
    {
        $entity = new TranslatableEntity();
        $entity->translate('en')
            ->setTitle('awesome');
        $entity->translate('ru')
            ->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $entity */
        $entity = $this->translatableRepository->find($id);

        $this->assertSame('awesome', $entity->translate('en')->getTitle());
        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());

        $entity->translate('en')
            ->setTitle('great');
        $entity->translate('fr', false)
            ->setTitle('fabuleux');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();

        /** @var TranslatableEntity $entity */
        $entity = $this->translatableRepository->find($id);

        $this->assertSame('great', $entity->translate('en')->getTitle());
        $this->assertSame('fabuleux', $entity->translate('fr')->getTitle());
        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());
    }

    public function testTranslateMethodShouldAlwaysReturnTranslationObject(): void
    {
        $translatableEntity = new TranslatableEntity();

        $this->assertInstanceOf(TranslatableEntityTranslation::class, $translatableEntity->translate('fr'));
    }

    public function testSubscriberShouldConfigureEntityWithCurrentLocale(): void
    {
        $entity = new TranslatableEntity();

        // magic method
        $entity->setTitle('test');

        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);

        $this->entityManager->flush();

        $id = $entity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $entity */
        $entity = $this->translatableRepository->find($id);

        $this->assertSame('en', $entity->getCurrentLocale());
        $this->assertSame('test', $entity->getTitle());
        $this->assertSame('test', $entity->translate($entity->getCurrentLocale())->getTitle());
    }

    public function testSubscriberShouldConfigureEntityWithDefaultLocale(): void
    {
        $entity = new TranslatableEntity();
        // magic method
        $entity->setTitle('test');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);

        $this->entityManager->flush();

        $id = $entity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $entity */
        $entity = $this->translatableRepository->find($id);

        $this->assertSame('en', $entity->getDefaultLocale());
        // magic method
        $this->assertSame('test', $entity->getTitle());

        $this->assertSame('test', $entity->translate($entity->getDefaultLocale())->getTitle());
        $this->assertSame('test', $entity->translate('fr')->getTitle());
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
        $entity = new TranslatableEntity();
        $entity->translate('fr')
            ->setTitle('fabuleux');
        $entity->translate('en')
            ->setTitle('');
        $entity->translate('ru')
            ->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->entityManager->clear();

        $entity = $this->translatableRepository->find($id);

        $this->assertSame('fabuleux', $entity->translate('fr')->getTitle());

        // empty English translation
        $this->assertNull($entity->translate('en')->getTitle());

        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());
    }

    public function testShouldRemoveTranslationsWhichBecomeEmpty(): void
    {
        $entity = new TranslatableEntity();
        $entity->translate('fr')
            ->setTitle('fabuleux');
        $entity->translate('en')
            ->setTitle('awesome');
        $entity->translate('ru')
            ->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $entity->translate('en')
            ->setTitle('');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->entityManager->clear();

        $entity = $this->translatableRepository->find($id);

        $this->assertSame('fabuleux', $entity->translate('fr')->getTitle());
        $this->assertNull($entity->translate('en')->getTitle());
        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());
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

    /**
     * Asserts that the one to many relationship between translatable and translations is mapped correctly.
     */
    private function assertTranslationsOneToManyMapped(string $translatableClass, string $translationClass): void
    {
        $meta = $this->entityManager->getClassMetadata($translationClass);
        $this->assertSame($translatableClass, $meta->getAssociationTargetClass('translatable'));

        $meta = $this->entityManager->getClassMetadata($translatableClass);
        $this->assertSame($translationClass, $meta->getAssociationTargetClass('translations'));

        $this->assertTrue($meta->isAssociationInverseSide('translations'));

        $this->assertSame(ClassMetadataInfo::ONE_TO_MANY, $meta->getAssociationMapping('translations')['type']);
    }
}
