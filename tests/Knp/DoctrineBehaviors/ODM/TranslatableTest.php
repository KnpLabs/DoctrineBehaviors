<?php

namespace Tests\Knp\DoctrineBehaviors\ODM;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

require_once 'DocumentManagerProvider.php';

class TranslatableTest extends \PHPUnit_Framework_TestCase
{
    use DocumentManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\TranslatableEntity',
            'BehaviorFixtures\\ORM\\TranslatableEntityTranslation'
        ];
    }

    protected function getEventManager()
    {
        $dm = new EventManager;

        $dm->addEventSubscriber(new \Knp\DoctrineBehaviors\ODM\Translatable\TranslatableListener(function() {
            return 'en';
        }));

        return $dm;
    }

    /**
     * @test
     */
    public function should_persist_translations()
    {
        $dm = $this->getDocumentManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();
        $entity->translate('fr')->setTitle('fabuleux');
        $entity->translate('en')->setTitle('awesome');
        $entity->translate('ru')->setTitle('удивительный');
        $entity->mergeNewTranslations();

        $dm->persist($entity);
        $dm->flush();
        $id = $entity->getId();
        $dm->clear();

        $entity = $dm
            ->getRepository('BehaviorFixtures\ORM\TranslatableEntity')
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
        die;
    }

    /**
     * @test
     */
    public function translate_method_should_always_return_translation_object()
    {
        $dm = $this->getDocumentManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();

        $this->assertInstanceOf(
            'BehaviorFixtures\ORM\TranslatableEntityTranslation',
            $entity->translate('fr')
        );
    }

    /**
     * @test
     */
    public function listener_should_configure_entity_with_current_locale()
    {
        $dm = $this->getDocumentManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();
        $entity->setTitle('test'); // magic method
        $entity->mergeNewTranslations();
        $dm->persist($entity);
        $dm->flush();
        $id = $entity->getId();
        $dm->clear();

        $entity = $dm->getRepository('BehaviorFixtures\ORM\TranslatableEntity')->find($id);

        $this->assertEquals('en', $entity->getCurrentLocale());
        $this->assertEquals('test', $entity->getTitle());
        $this->assertEquals('test', $entity->translate($entity->getCurrentLocale())->getTitle());
    }

    /**
     * @test
     */
    public function should_have_oneToMany_relation()
    {
        $dm = $this->getDocumentManager();

        $meta = $dm->getClassMetadata('BehaviorFixtures\ORM\TranslatableEntity');
        $this->assertTrue(
            $meta->hasAssociation('translations')
        );
    }

    /**
     * @test
     */
    public function should_create_only_one_time_the_same_translation()
    {
        $dm = $this->getDocumentManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();
        $translation = $entity->translate('fr');
        $translation->setTitle('fabuleux');
        $entity->translate('fr')->setTitle('fabuleux2');
        $entity->translate('fr')->setTitle('fabuleux3');

        $this->assertEquals('fabuleux3', $entity->translate('fr')->getTitle());
        $this->assertEquals(spl_object_hash($entity->translate('fr')), spl_object_hash($translation));
    }
}
