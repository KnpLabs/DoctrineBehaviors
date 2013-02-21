<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultTranslatableTest.php';

class RenamedTranslatableTest extends DefaultTranslatableTest
{
    protected function getTestedTranslatableEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedTranslatableEntity";
    }

    protected function getTestedTranslationEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedTranslatableEntityTranslation";
    }

    /**
     * @test
     */
    public function should_persist_translations()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedTranslatableEntity();
        $entity->translateTrait('fr')->setTitle('fabuleux');
        $entity->translateTrait('en')->setTitle('awesome');
        $entity->translateTrait('ru')->setTitle('удивительный');
        $entity->mergeTraitNewTranslations();

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
            $entity->translateTrait('fr')->getTitle()
        );

        $this->assertEquals(
            'awesome',
            $entity->translateTrait('en')->getTitle()
        );

        $this->assertEquals(
            'удивительный',
            $entity->translateTrait('ru')->getTitle()
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
            $entity->translateTrait('fr')
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
        $entity->mergeTraitNewTranslations();
        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $entity = $em->getRepository($this->getTestedTranslatableEntityClass())->find($id);

        $this->assertEquals('en', $entity->getTraitCurrentLocale());
        $this->assertEquals('test', $entity->getTitle());
        $this->assertEquals('test', $entity->translateTrait($entity->getTraitCurrentLocale())->getTitle());
    }

    /**
     * @test
     */
    public function should_create_only_one_time_the_same_translation()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedTranslatableEntity();
        $translation = $entity->translateTrait('fr');
        $translation->setTitle('fabuleux');
        $entity->translateTrait('fr')->setTitle('fabuleux2');
        $entity->translateTrait('fr')->setTitle('fabuleux3');

        $this->assertEquals('fabuleux3', $entity->translateTrait('fr')->getTitle());
        $this->assertEquals(spl_object_hash($entity->translateTrait('fr')), spl_object_hash($translation));
    }

    /**
     * @test
     */
    public function should_remove_translation()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedTranslatableEntity();
        $entity->translateTrait('en')->setTitle('Hello');
        $entity->translateTrait('nl')->setTitle('Hallo');
        $entity->mergeTraitNewTranslations();
        $em->persist($entity);
        $em->flush();

        $nlTranslation = $entity->translateTrait('nl');
        $entity->removeTraitTranslation($nlTranslation);
        $em->flush();

        $em->refresh($entity);
        $this->assertNotEquals('Hallo', $entity->translateTrait('nl')->getTitle());
    }
}
