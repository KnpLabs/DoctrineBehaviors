<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
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
        $entity->translate('fr')->setTitle('fabuleux');
        $entity->translate('en')->setTitle('awesome');
        $entity->translate('ru')->setTitle('удивительный');
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
        $entity->translate('en', false)->setTitle('plastic bag');
        $entity->translate('fr', false)->setTitle('sac plastique');
        $entity->translate('fr_CH', false)->setTitle('cornet');
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
        $entity->translate('en')->setTitle('awesome');
        $entity->translate('ru')->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $id = $entity->getId();
        $this->entityManager->clear();

        /** @var TranslatableEntity $entity */
        $entity = $this->translatableRepository->find($id);

        $this->assertSame('awesome', $entity->translate('en')->getTitle());
        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());

        $entity->translate('en')->setTitle('great');
        $entity->translate('fr', false)->setTitle('fabuleux');
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
        $entity = new TranslatableEntity();

        $this->assertInstanceOf(TranslatableEntityTranslation::class, $entity->translate('fr'));
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
        $entity->setTitle('test'); // magic method
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
        $entity = new TranslatableEntity();
        $translation = $entity->translate('fr');
        $translation->setTitle('fabuleux');
        $entity->translate('fr')->setTitle('fabuleux2');
        $entity->translate('fr')->setTitle('fabuleux3');

        $this->assertSame('fabuleux3', $entity->translate('fr')->getTitle());
        $this->assertSame(spl_object_hash($entity->translate('fr')), spl_object_hash($translation));
    }

    public function testShouldRemoveTranslation(): void
    {
        $entity = new TranslatableEntity();
        $entity->translate('en')->setTitle('Hello');
        $entity->translate('nl')->setTitle('Hallo');
        $entity->mergeNewTranslations();
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $nlTranslation = $entity->translate('nl');
        $entity->removeTranslation($nlTranslation);
        $this->entityManager->flush();

        $this->entityManager->refresh($entity);
        $this->assertNotSame('Hallo', $entity->translate('nl')->getTitle());
    }

    public function testSetTranslations(): void
    {
        $entity = new TranslatableEntity();
        $translation = $entity->translate('en');

        $entity->setTranslations([$translation]);

        $this->assertCount(1, $entity->getTranslations());
    }

    public function testShouldNotPersistNewEmptyTranslations(): void
    {
        $entity = new TranslatableEntity();
        $entity->translate('fr')->setTitle('fabuleux');
        $entity->translate('en')->setTitle('');
        $entity->translate('ru')->setTitle('удивительный');
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
        $entity->translate('fr')->setTitle('fabuleux');
        $entity->translate('en')->setTitle('awesome');
        $entity->translate('ru')->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $entity->translate('en')->setTitle('');
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
