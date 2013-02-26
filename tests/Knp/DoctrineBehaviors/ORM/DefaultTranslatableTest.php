<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

require_once 'EntityManagerProvider.php';

class DefaultTranslatableTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return [
            $this->getTestedTranslatableEntityClass(),
            $this->getTestedTranslationEntityClass()
        ];
    }

    protected function getTestedTranslatableEntityClass()
    {
        return "\BehaviorFixtures\ORM\DefaultTranslatableEntity";
    }
    
    protected function getTestedTranslatableEntity()
    {
        $class = $this->getTestedTranslatableEntityClass();
        return new $class;
    }

    protected function getTestedTranslationEntityClass()
    {
        return "\BehaviorFixtures\ORM\DefaultTranslatableEntityTranslation";
    }

    protected function getTestedTranslationEntity()
    {
        $class = $this->getTestedTranslationEntityClass();
        return new $class;
    }

    protected function getEventManager()
    {
        $em = new EventManager;

        $em->addEventSubscriber(new \Knp\DoctrineBehaviors\ORM\Translatable\TranslatableListener(
            new ClassAnalyzer(),
            function()
            {
                return 'en';
            }
        ));

        return $em;
    }

    /**
     * @test
     */
    public function should_persist_translations()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedTranslatableEntity();
        $entity->translate('fr')->setTitle('fabuleux');
        $entity->translate('en')->setTitle('awesome');
        $entity->translate('ru')->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $entity = $em
            ->getRepository($this->getTestedTranslatableEntityClass())
            ->find($id)
        ;

        $this->assertEquals(
            'fabuleux',
            $entity->translate('fr')->getTitle()
        );

        $this->assertEquals(
            'awesome',
            $entity->translate('en')->getTitle()
        );

        $this->assertEquals(
            'удивительный',
            $entity->translate('ru')->getTitle()
        );
    }

    /**
     * @test
     */
    public function translate_method_should_always_return_translation_object()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedTranslatableEntity();

        $this->assertInstanceOf(
            $this->getTestedTranslationEntityClass(),
            $entity->translate('fr')
        );
    }

    /**
     * @test
     */
    public function listener_should_configure_entity_with_current_locale()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedTranslatableEntity();
        $entity->setTitle('test'); // magic method
        $entity->mergeNewTranslations();
        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $entity = $em->getRepository($this->getTestedTranslatableEntityClass())->find($id);

        $this->assertEquals('en', $entity->getCurrentLocale());
        $this->assertEquals('test', $entity->getTitle());
        $this->assertEquals('test', $entity->translate($entity->getCurrentLocale())->getTitle());
    }

    /**
     * @test
     */
    public function should_have_oneToMany_relation()
    {
        $em = $this->getEntityManager();

        $meta = $em->getClassMetadata($this->getTestedTranslationEntityClass());
        $this->assertEquals(
            substr($this->getTestedTranslatableEntityClass(), 1),
            $meta->getAssociationTargetClass('translatable')
        );

        $meta = $em->getClassMetadata($this->getTestedTranslatableEntityClass());
        $this->assertEquals(
            substr($this->getTestedTranslationEntityClass(), 1),
            $meta->getAssociationTargetClass('translations')
        );
        $this->assertTrue($meta->isAssociationInverseSide('translations'));

        $this->assertEquals(
            ClassMetadataInfo::ONE_TO_MANY,
            $meta->getAssociationMapping('translations')['type']
        );
    }

    /**
     * @test
     */
    public function should_create_only_one_time_the_same_translation()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedTranslatableEntity();
        $translation = $entity->translate('fr');
        $translation->setTitle('fabuleux');
        $entity->translate('fr')->setTitle('fabuleux2');
        $entity->translate('fr')->setTitle('fabuleux3');

        $this->assertEquals('fabuleux3', $entity->translate('fr')->getTitle());
        $this->assertEquals(spl_object_hash($entity->translate('fr')), spl_object_hash($translation));
    }

    /**
     * @test
     */
    public function should_remove_translation()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedTranslatableEntity();
        $entity->translate('en')->setTitle('Hello');
        $entity->translate('nl')->setTitle('Hallo');
        $entity->mergeNewTranslations();
        $em->persist($entity);
        $em->flush();

        $nlTranslation = $entity->translate('nl');
        $entity->removeTranslation($nlTranslation);
        $em->flush();

        $em->refresh($entity);
        $this->assertNotEquals('Hallo', $entity->translate('nl')->getTitle());
    }
}
