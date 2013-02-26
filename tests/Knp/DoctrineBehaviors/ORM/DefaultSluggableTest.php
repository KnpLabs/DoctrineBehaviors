<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Doctrine\Common\EventManager;

require_once 'EntityManagerProvider.php';

class DefaultSluggableTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return array(
            $this->getTestedEntityClass()
        );
    }

    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\DefaultSluggableEntity";
    }

    protected function getTestedEntity()
    {
        $class = $this->getTestedEntityClass();
        return new $class;
    }

    protected function getEventManager()
    {
        $em = new EventManager;

        $em->addEventSubscriber(
            new \Knp\DoctrineBehaviors\ORM\Sluggable\SluggableListener(new ClassAnalyzer())
        );

        return $em;
    }

    public function testSlugLoading()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedEntity();

        $expected = 'the-name';

        $entity->setName('The name');

        $em->persist($entity);
        $em->flush();

        $this->assertNotNull($id = $entity->getId());

        $em->clear();

        $entity = $em->getRepository($this->getTestedEntityClass())->find($id);

        $this->assertNotNull($entity);
        $this->assertEquals($entity->getSlug(), $expected);
    }

    public function testNotUpdatedSlug()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedEntity();

        $expected = 'the-name';

        $entity->setName('The name');

        $em->persist($entity);
        $em->flush();

        $entity->setDate(new \DateTime);

        $em->persist($entity);
        $em->flush();

        $this->assertEquals($entity->getSlug(), $expected);
    }

    public function testUpdatedSlug()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedEntity();

        $expected = 'the-name';

        $entity->setName('The name');

        $em->persist($entity);
        $em->flush();

        $this->assertEquals($entity->getSlug(), $expected);

        $expected = 'the-name-2';

        $entity->setName('The name 2');

        $em->persist($entity);
        $em->flush();
        
        $this->assertEquals($entity->getSlug(), $expected);
    }
}
