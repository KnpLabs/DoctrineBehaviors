<?php

namespace Tests\Knp\DoctrineBehaviors\ORM\Translatable;

use BehaviorFixtures\ORM\ExtendedTranslatableEntity;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use tests\Knp\DoctrineBehaviors\ORM\EntityManagerProvider;

require_once __DIR__.'/../EntityManagerProvider.php';

class TranslatableInheritanceTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\ExtendedTranslatableEntity',
            'BehaviorFixtures\\ORM\\ExtendedTranslatableEntityTranslation',
        ];
    }

    protected function getEventManager()
    {
        $em = new EventManager;

        $em->addEventSubscriber(new \Knp\DoctrineBehaviors\ORM\Translatable\TranslatableSubscriber(
            new ClassAnalyzer(),
            function()
            {
                return 'en';
            },
            function()
            {
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
     * @test
     */
    public function should_persist_translations_with_inheritance()
    {
        $em = $this->getEntityManager();

        $entity = new ExtendedTranslatableEntity();
        $entity->translate('fr')->setTitle('fabuleux');
        $entity->translate('fr')->setExtendedTitle('fabuleux');
        $entity->translate('en')->setTitle('awesome');
        $entity->translate('en')->setExtendedTitle('awesome');
        $entity->translate('ru')->setTitle('удивительный');
        $entity->translate('ru')->setExtendedTitle('удивительный');
        $entity->mergeNewTranslations();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $entity = $em
            ->getRepository(ExtendedTranslatableEntity::class)
            ->find($id)
        ;

        $this->assertEquals(
            'fabuleux',
            $entity->translate('fr')->getTitle()
        );

        $this->assertEquals(
            'fabuleux',
            $entity->translate('fr')->getExtendedTitle()
        );

        $this->assertEquals(
            'awesome',
            $entity->translate('en')->getTitle()
        );

        $this->assertEquals(
            'awesome',
            $entity->translate('en')->getExtendedTitle()
        );

        $this->assertEquals(
            'удивительный',
            $entity->translate('ru')->getTitle()
        );

        $this->assertEquals(
            'удивительный',
            $entity->translate('ru')->getExtendedTitle()
        );
    }

    /**
     * Asserts that the one to many relationship between translatable and translations is mapped correctly.
     *
     * @param string $translatableClass The class name of the translatable entity
     * @param string $translationClass  The class name of the translation entity
     */
    private function assertTranslationsOneToManyMapped($translatableClass, $translationClass)
    {
        $em = $this->getEntityManager();

        $meta = $em->getClassMetadata($translationClass);
        $this->assertEquals($translatableClass, $meta->getAssociationTargetClass('translatable'));

        $meta = $em->getClassMetadata($translatableClass);
        $this->assertEquals($translationClass, $meta->getAssociationTargetClass('translations'));

        $this->assertTrue($meta->isAssociationInverseSide('translations'));

        $this->assertEquals(
            ClassMetadataInfo::ONE_TO_MANY,
            $meta->getAssociationMapping('translations')['type']
        );
    }
}
