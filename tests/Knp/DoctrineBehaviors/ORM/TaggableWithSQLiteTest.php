<?php

namespace tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use PHPUnit_Framework_TestCase;
use BehaviorFixtures\ORM\TaggableEntity;
use Knp\DoctrineBehaviors\ORM\Taggable\TaggableSubscriber;

require_once 'EntityManagerProvider.php';

class TaggableWithSQLiteTest extends PHPUnit_Framework_TestCase
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

    protected $conn;

    protected function setUp()
    {
        $this->conn = [
            'path' => __DIR__ . '/../../../temp/taggable.db',
        ];
    }

    public function testAddTag()
    {
        $entity = new TaggableEntity;
        $entity->addTag('php');
        $this->assertEquals(1, $entity->getTags()->count());
    }

    public function testAddTags()
    {
        $entity = new TaggableEntity;
        $entity->addTags('php, tag, putin, vodka, balalaika, darth-vader');
        $this->assertEquals(6, $entity->getTags()->count());
    }

    public function testSetTags()
    {
        $entity = new TaggableEntity;
        $entity->addTags('php, tag');
        $this->assertEquals(2, $entity->getTags()->count());
        $entity->addTags('js, front, browser');
        $this->assertEquals(5, $entity->getTags()->count());
        $entity->setTags('snow, google, sankcii, caviar');
        $this->assertEquals(4, $entity->getTags()->count());
    }

    public function testRemoveTag()
    {
        $entity = new TaggableEntity;
        $entity->addTags('sex, drugs, rock-n-roll');
        $this->assertEquals(3, $entity->getTags()->count());
        $entity->removeTag('drugs');
        $this->assertEquals(2, $entity->getTags()->count());
    }

    public function testRemoveTags()
    {
        $entity = new TaggableEntity;
        $entity->addTags('one, two, three, five, ten, nine');
        $this->assertEquals(6, $entity->getTags()->count());
        $entity->removeTags('two, five');
        $this->assertEquals(4, $entity->getTags()->count());
    }

    public function testClearTags()
    {
        $entity = new TaggableEntity;
        $entity->addTags('php, tag, putin, vodka, balalaika, darth-vader');
        $this->assertEquals(6, $entity->getTags()->count());
        $entity->clearTags();
        $this->assertEquals(0, $entity->getTags()->count());
    }

    public function testPersistTag()
    {
        /**
         * @var \BehaviorFixtures\ORM\TaggableEntity $savedEntity
         */
        $this->getEntityManager(null, null, $this->conn);
        $em = $this->getEntityManager();
        $entity = new TaggableEntity();
        $entity->addTag('symfony');
        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $savedEntity = $em->getRepository('BehaviorFixtures\\ORM\\TaggableEntity')
            ->find($id);
        $this->assertEquals(1, $savedEntity->getTags()->count());
    }

    public function testCanonizationTag()
    {
        /**
         * @var \BehaviorFixtures\ORM\TaggableEntityTag $tag
         */
        $this->getEntityManager(null, null, $this->conn);
        $em = $this->getEntityManager();
        $entity = new TaggableEntity();
        $tag = ' bla-bla-bla ';
        $entity->addTag($tag);
        $em->persist($entity);
        $em->flush();
        $tag = $em->getRepository('BehaviorFixtures\\ORM\\TaggableEntityTag')
            ->findOneBy(['nameCanonical' => 'blablabla']);
        $this->assertNotEmpty($tag);
    }

    public function testRemovePersistedTag()
    {
        /**
         * @var \BehaviorFixtures\ORM\TaggableEntity $savedEntity
         * @var \BehaviorFixtures\ORM\TaggableEntity $savedAgainEntity
         */
        $this->getEntityManager(null, null, $this->conn);
        $em = $this->getEntityManager();
        $entity = new TaggableEntity();
        $entity->addTag('vodka, water');
        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $savedEntity = $em->getRepository('BehaviorFixtures\\ORM\\TaggableEntity')
            ->find($id);
        $this->assertEquals(2, $savedEntity->getTags()->count());
        $savedEntity->removeTag('water');
        $em->persist($entity);
        $em->flush();
        $savedAgainEntity = $em->getRepository('BehaviorFixtures\\ORM\\TaggableEntity')
            ->find($id);
        $this->assertEquals(1, $savedAgainEntity->getTags()->count());
    }

    public function testClearPersistedTag()
    {
        /**
         * @var \BehaviorFixtures\ORM\TaggableEntity $savedEntity
         * @var \BehaviorFixtures\ORM\TaggableEntity $clearEntity
         */
        $this->getEntityManager(null, null, $this->conn);
        $em = $this->getEntityManager();
        $entity = new TaggableEntity();
        $entity->addTag('save, clear');
        $em->persist($entity);
        $em->flush();
        $id = $entity->getId();
        $savedEntity = $em->getRepository('BehaviorFixtures\\ORM\\TaggableEntity')
            ->find($id);
        $this->assertEquals(2, $savedEntity->getTags()->count());
        $savedEntity->clearTags();
        $em->persist($savedEntity);
        $em->flush();
        $clearEntity = $em->getRepository('BehaviorFixtures\\ORM\\TaggableEntity')
            ->find($id);
        $this->assertEquals(0, $clearEntity->getTags()->count());
    }
}
