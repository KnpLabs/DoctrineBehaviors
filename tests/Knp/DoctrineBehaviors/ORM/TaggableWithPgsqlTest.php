<?php

namespace tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use PHPUnit_Framework_TestCase;
use BehaviorFixtures\ORM\TaggableEntity;
use Knp\DoctrineBehaviors\ORM\Taggable\TaggableSubscriber;

require_once 'EntityManagerProvider.php';

class TaggableWithPgsqlTest extends PHPUnit_Framework_TestCase
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
                'Knp\\DoctrineBehaviors\\Model\\Taggable\\Taggable',
                'Knp\\DoctrineBehaviors\\Model\\Taggable\\Tag',
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
            'driver' => 'pdo_pgsql',
            'dbname' => 'orm_behaviors_test',
        ];
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
