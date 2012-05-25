<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

require_once 'EntityManagerProvider.php';

class TranslatableTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\TranslatableEntity',
            'BehaviorFixtures\\ORM\\TranslatableEntityTranslation'
        ];
    }

    protected function getEventManager()
    {
        $em = new EventManager;

        $em->addEventSubscriber(new \Knp\DoctrineBehaviors\ORM\Translatable\TranslatableListener(function() {
            return 'en';
        }));

        return $em;
    }

    /**
     * @test
     */
    public function should_persist_translations()
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
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TranslatableEntity();
        $entity->setTitle('test'); // magic method
        $entity->mergeNewTranslations();
        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();

        $entity = $em->getRepository('BehaviorFixtures\ORM\TranslatableEntity')->find($id);

        $this->assertEquals('en', $entity->getCurrentLocale());
        $this->assertEquals('test', $entity->getTitle());
        $this->assertEquals('test', $entity->translate($entity->getCurrentLocale())->getTitle());
    }

    /**
     * @test
     */
    public function should_have_ontToMany_relation()
    {
        $em = $this->getEntityManager();

        $meta = $em->getClassMetadata('BehaviorFixtures\ORM\TranslatableEntityTranslation');
        $this->assertEquals(
            'BehaviorFixtures\ORM\TranslatableEntity',
            $meta->getAssociationTargetClass('translatable')
        );

        $meta = $em->getClassMetadata('BehaviorFixtures\ORM\TranslatableEntity');
        $this->assertEquals(
            'BehaviorFixtures\ORM\TranslatableEntityTranslation',
            $meta->getAssociationTargetClass('translations')
        );
        $this->assertTrue($meta->isAssociationInverseSide('translations'));

        $this->assertEquals(
            ClassMetadataInfo::ONE_TO_MANY,
            $meta->getAssociationMapping('translations')['type']
        );
    }
}
