<?php

namespace tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use PHPUnit_Framework_TestCase;
use BehaviorFixtures\ORM\TaggableEntity;
use Knp\DoctrineBehaviors\ORM\Taggable\TaggableSubscriber;

require_once 'EntityManagerProvider.php';

class TaggableTest extends PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return [
            'BehaviorFixtures\\ORM\\TaggableEntity',
            'BehaviorFixtures\\ORM\\TaggableEntityTag',
        ];
    }

    protected function getEventManager()
    {
        $em = new EventManager();
        $em->addEventSubscriber(
            new TaggableSubscriber(
                new ClassAnalyzer(),
                false,
                'Knp\DoctrineBehaviors\Model\Taggable\Taggable',
                'Knp\DoctrineBehaviors\Model\Taggable\Tag',
                'LAZY',
                'LAZY',
                'Tag'
            ));

        return $em;
    }

    protected function setUp()
    {
        $conn = [
            'path' => __DIR__ . '/../../../temp/taggable.db',
        ];
        $this->getEntityManager(null, null, $conn);
    }

    public function testAddTag()
    {
        $entity = new TaggableEntity;
        $entity->addTagFromString('php');
        $this->assertEquals(1, $entity->getTags()->count());
    }

    public function testPersistTag()
    {
        $em = $this->getEntityManager();
        $entity = new TaggableEntity();
        $entity->addTagFromString('php');
        $entity->addTagFromString('some string');
        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $em->clear();
    }
}
