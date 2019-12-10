<?php

declare(strict_types=1);

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

require_once 'EntityManagerProvider.php';

class TranslatableTest extends \PHPUnit\Framework\TestCase
{
    use EntityManagerProvider;

    public function testShouldPersistTranslations(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();
        $entity->translate('fr')->setTitle('fabuleux');
        $entity->translate('en')->setTitle('awesome');
        $entity->translate('ru')->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $entity = $em
            ->getRepository('BehaviorFixtures\ORM\TranslatableEntity')
            ->find($id)
        ;

        $this->assertSame(
            'fabuleux',
            $entity->translate('fr')->getTitle()
        );

        $this->assertSame(
            'awesome',
            $entity->translate('en')->getTitle()
        );

        $this->assertSame(
            'удивительный',
            $entity->translate('ru')->getTitle()
        );
    }

    public function testShouldFallbackCountryLocaleToLanguageOnlyTranslation(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();
        $entity->translate('en', false)->setTitle('plastic bag');
        $entity->translate('fr', false)->setTitle('sac plastique');
        $entity->translate('fr_CH', false)->setTitle('cornet');
        $entity->mergeNewTranslations();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $entity = $em
            ->getRepository('BehaviorFixtures\ORM\TranslatableEntity')
            ->find($id)
        ;

        $this->assertSame(
            'plastic bag',
            $entity->translate('de')->getTitle()
        );

        $this->assertSame(
            'sac plastique',
            $entity->translate('fr_FR')->getTitle()
        );

        $this->assertSame(
            'cornet',
            $entity->translate('fr_CH')->getTitle()
        );
    }

    public function testShouldUpdateAndAddNewTranslations(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();
        $entity->translate('en')->setTitle('awesome');
        $entity->translate('ru')->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $entity = $em
            ->getRepository('BehaviorFixtures\ORM\TranslatableEntity')
            ->find($id)
        ;

        $this->assertSame(
            'awesome',
            $entity->translate('en')->getTitle()
        );

        $this->assertSame(
            'удивительный',
            $entity->translate('ru')->getTitle()
        );

        $entity->translate('en')->setTitle('great');
        $entity->translate('fr', false)->setTitle('fabuleux');
        $entity->mergeNewTranslations();

        $em->persist($entity);
        $em->flush();
        $em->clear();

        $entity = $em
            ->getRepository('BehaviorFixtures\ORM\TranslatableEntity')
            ->find($id)
        ;

        $this->assertSame(
            'great',
            $entity->translate('en')->getTitle()
        );

        $this->assertSame(
            'fabuleux',
            $entity->translate('fr')->getTitle()
        );

        $this->assertSame(
            'удивительный',
            $entity->translate('ru')->getTitle()
        );
    }

    public function testTranslateMethodShouldAlwaysReturnTranslationObject(): void
    {
        $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();

        $this->assertInstanceOf(
            'BehaviorFixtures\ORM\TranslatableEntityTranslation',
            $entity->translate('fr')
        );
    }

    public function testSubscriberShouldConfigureEntityWithCurrentLocale(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();
        $entity->setTitle('test'); // magic method
        $entity->mergeNewTranslations();
        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $entity = $em->getRepository('BehaviorFixtures\ORM\TranslatableEntity')->find($id);

        $this->assertSame('en', $entity->getCurrentLocale());
        $this->assertSame('test', $entity->getTitle());
        $this->assertSame('test', $entity->translate($entity->getCurrentLocale())->getTitle());
    }

    public function testSubscriberShouldConfigureEntityWithDefaultLocale(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();
        $entity->setTitle('test'); // magic method
        $entity->mergeNewTranslations();
        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $entity = $em->getRepository('BehaviorFixtures\ORM\TranslatableEntity')->find($id);

        $this->assertSame('en', $entity->getDefaultLocale());
        $this->assertSame('test', $entity->getTitle());
        $this->assertSame('test', $entity->translate($entity->getDefaultLocale())->getTitle());
        $this->assertSame('test', $entity->translate('fr')->getTitle());
    }

    public function testShouldHaveOneToManyRelation(): void
    {
        $this->assertTranslationsOneToManyMapped(
            'BehaviorFixtures\ORM\TranslatableEntity',
            'BehaviorFixtures\ORM\TranslatableEntityTranslation'
        );
    }

    public function testShouldHaveOneToManyRelationWhenTranslationClassNameIsCustom(): void
    {
        $this->assertTranslationsOneToManyMapped(
            'BehaviorFixtures\ORM\TranslatableCustomizedEntity',
            'BehaviorFixtures\ORM\Translation\TranslatableCustomizedEntityTranslation'
        );
    }

    public function testShouldCreateOnlyOneTimeTheSameTranslation(): void
    {
        $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();
        $translation = $entity->translate('fr');
        $translation->setTitle('fabuleux');
        $entity->translate('fr')->setTitle('fabuleux2');
        $entity->translate('fr')->setTitle('fabuleux3');

        $this->assertSame('fabuleux3', $entity->translate('fr')->getTitle());
        $this->assertSame(spl_object_hash($entity->translate('fr')), spl_object_hash($translation));
    }

    public function testShouldRemoveTranslation(): void
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();
        $entity->translate('en')->setTitle('Hello');
        $entity->translate('nl')->setTitle('Hallo');
        $entity->mergeNewTranslations();
        $em->persist($entity);
        $em->flush();

        $nlTranslation = $entity->translate('nl');
        $entity->removeTranslation($nlTranslation);
        $em->flush();

        $em->refresh($entity);
        $this->assertNotSame('Hallo', $entity->translate('nl')->getTitle());
    }

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\TranslatableEntity',
            'BehaviorFixtures\\ORM\\TranslatableEntityTranslation',
        ];
    }

    protected function getEventManager()
    {
        $em = new EventManager();

        $em->addEventSubscriber(new \Knp\DoctrineBehaviors\ORM\Translatable\TranslatableSubscriber(
            new ClassAnalyzer(),
            function () {
                return 'en';
            },
            function () {
                return 'en';
            },
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'Knp\DoctrineBehaviors\Model\Translatable\Translation',
            'LAZY',
            'LAZY'
        ));

        return $em;
    }

    /**
     * Asserts that the one to many relationship between translatable and translations is mapped correctly.
     *
     * @param string $translatableClass The class name of the translatable entity
     * @param string $translationClass  The class name of the translation entity
     */
    private function assertTranslationsOneToManyMapped($translatableClass, $translationClass): void
    {
        $em = $this->getEntityManager();

        $meta = $em->getClassMetadata($translationClass);
        $this->assertSame($translatableClass, $meta->getAssociationTargetClass('translatable'));

        $meta = $em->getClassMetadata($translatableClass);
        $this->assertSame($translationClass, $meta->getAssociationTargetClass('translations'));

        $this->assertTrue($meta->isAssociationInverseSide('translations'));

        $this->assertSame(
            ClassMetadataInfo::ONE_TO_MANY,
            $meta->getAssociationMapping('translations')['type']
        );
    }
}
