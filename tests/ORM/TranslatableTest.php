<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use Knp\DoctrineBehaviors\Model\Translatable\Translation;
use Knp\DoctrineBehaviors\ORM\Translatable\TranslatableSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\TranslatableCustomizedEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\TranslatableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\TranslatableEntityTranslation;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\Translation\TranslatableCustomizedEntityTranslation;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EntityManagerProvider.php';

final class TranslatableTest extends TestCase
{
    use EntityManagerProvider;

    public function testShouldPersistTranslations(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new TranslatableEntity();
        $entity->translate('fr')->setTitle('fabuleux');
        $entity->translate('en')->setTitle('awesome');
        $entity->translate('ru')->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $entityManager->persist($entity);
        $entityManager->flush();
        $id = $entity->getId();
        $entityManager->clear();

        $entity = $entityManager
            ->getRepository(TranslatableEntity::class)
            ->find($id)
        ;

        $this->assertSame('fabuleux', $entity->translate('fr')->getTitle());

        $this->assertSame('awesome', $entity->translate('en')->getTitle());

        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());
    }

    public function testShouldFallbackCountryLocaleToLanguageOnlyTranslation(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new TranslatableEntity();
        $entity->translate('en', false)->setTitle('plastic bag');
        $entity->translate('fr', false)->setTitle('sac plastique');
        $entity->translate('fr_CH', false)->setTitle('cornet');
        $entity->mergeNewTranslations();

        $entityManager->persist($entity);
        $entityManager->flush();
        $id = $entity->getId();
        $entityManager->clear();

        $entity = $entityManager
            ->getRepository(TranslatableEntity::class)
            ->find($id)
        ;

        $this->assertSame('plastic bag', $entity->translate('de')->getTitle());

        $this->assertSame('sac plastique', $entity->translate('fr_FR')->getTitle());

        $this->assertSame('cornet', $entity->translate('fr_CH')->getTitle());
    }

    public function testShouldUpdateAndAddNewTranslations(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new TranslatableEntity();
        $entity->translate('en')->setTitle('awesome');
        $entity->translate('ru')->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $entityManager->persist($entity);
        $entityManager->flush();
        $id = $entity->getId();
        $entityManager->clear();

        $entity = $entityManager
            ->getRepository(TranslatableEntity::class)
            ->find($id)
        ;

        $this->assertSame('awesome', $entity->translate('en')->getTitle());

        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());

        $entity->translate('en')->setTitle('great');
        $entity->translate('fr', false)->setTitle('fabuleux');
        $entity->mergeNewTranslations();

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $entity = $entityManager
            ->getRepository(TranslatableEntity::class)
            ->find($id)
        ;

        $this->assertSame('great', $entity->translate('en')->getTitle());

        $this->assertSame('fabuleux', $entity->translate('fr')->getTitle());

        $this->assertSame('удивительный', $entity->translate('ru')->getTitle());
    }

    public function testTranslateMethodShouldAlwaysReturnTranslationObject(): void
    {
        $this->getEntityManager();

        $entity = new TranslatableEntity();

        $this->assertInstanceOf(TranslatableEntityTranslation::class, $entity->translate('fr'));
    }

    public function testSubscriberShouldConfigureEntityWithCurrentLocale(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new TranslatableEntity();
        $entity->setTitle('test'); // magic method
        $entity->mergeNewTranslations();
        $entityManager->persist($entity);
        $entityManager->flush();
        $id = $entity->getId();
        $entityManager->clear();

        $entity = $entityManager->getRepository(TranslatableEntity::class)->find($id);

        $this->assertSame('en', $entity->getCurrentLocale());
        $this->assertSame('test', $entity->getTitle());
        $this->assertSame('test', $entity->translate($entity->getCurrentLocale())->getTitle());
    }

    public function testSubscriberShouldConfigureEntityWithDefaultLocale(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new TranslatableEntity();
        $entity->setTitle('test'); // magic method
        $entity->mergeNewTranslations();
        $entityManager->persist($entity);
        $entityManager->flush();
        $id = $entity->getId();
        $entityManager->clear();

        $entity = $entityManager->getRepository(TranslatableEntity::class)->find($id);

        $this->assertSame('en', $entity->getDefaultLocale());
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
        $this->getEntityManager();

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
        $entityManager = $this->getEntityManager();

        $entity = new TranslatableEntity();
        $entity->translate('en')->setTitle('Hello');
        $entity->translate('nl')->setTitle('Hallo');
        $entity->mergeNewTranslations();
        $entityManager->persist($entity);
        $entityManager->flush();

        $nlTranslation = $entity->translate('nl');
        $entity->removeTranslation($nlTranslation);
        $entityManager->flush();

        $entityManager->refresh($entity);
        $this->assertNotSame('Hallo', $entity->translate('nl')->getTitle());
    }

    protected function getUsedEntityFixtures()
    {
        return [TranslatableEntity::class, TranslatableEntityTranslation::class];
    }

    protected function getEventManager()
    {
        $eventManager = new EventManager();

        $eventManager->addEventSubscriber(new TranslatableSubscriber(
            new ClassAnalyzer(),
            function () {
                return 'en';
            },
            function () {
                return 'en';
            },
            Translatable::class,
            Translation::class,
            'LAZY',
            'LAZY',
            false
        ));

        return $eventManager;
    }

    /**
     * Asserts that the one to many relationship between translatable and translations is mapped correctly.
     */
    private function assertTranslationsOneToManyMapped(string $translatableClass, string $translationClass): void
    {
        $entityManager = $this->getEntityManager();

        $meta = $entityManager->getClassMetadata($translationClass);
        $this->assertSame($translatableClass, $meta->getAssociationTargetClass('translatable'));

        $meta = $entityManager->getClassMetadata($translatableClass);
        $this->assertSame($translationClass, $meta->getAssociationTargetClass('translations'));

        $this->assertTrue($meta->isAssociationInverseSide('translations'));

        $this->assertSame(ClassMetadataInfo::ONE_TO_MANY, $meta->getAssociationMapping('translations')['type']);
    }
}
