<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Doctrine\Common\EventManager;

require_once 'EntityManagerProvider.php';

class SluggableTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return array(
            'BehaviorFixtures\\ORM\\SluggableEntity'
        );
    }

    protected function getEventManager()
    {
        $em = new EventManager;

        $em->addEventSubscriber(
            new \Knp\DoctrineBehaviors\ORM\Sluggable\SluggableSubscriber(
                new ClassAnalyzer(),
                false,
                'Knp\DoctrineBehaviors\Model\Sluggable\Sluggable'
        ));

        return $em;
    }

    public function testSlugLoading()
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\SluggableEntity();

        $expected = 'the-name';

        $entity->setName('The name');

        $em->persist($entity);
        $em->flush();

        $this->assertNotNull($id = $entity->getId());

        $em->clear();

        $entity = $em->getRepository('BehaviorFixtures\ORM\SluggableEntity')->find($id);

        $this->assertNotNull($entity);
        $this->assertEquals($entity->getSlug(), $expected);
    }

    public function testNotUpdatedSlug()
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\SluggableEntity();

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

        $entity = new \BehaviorFixtures\ORM\SluggableEntity();

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

    /**
     * @test
     * @expectedException Exception
     */
    public function should_notice_deprecation()
    {
        set_error_handler(function() {throw new \Exception; }, E_USER_DEPRECATED);
        new \Knp\DoctrineBehaviors\ORM\Sluggable\SluggableListener;
    }

    public function tearDown()
    {
        restore_error_handler();
    }
}
