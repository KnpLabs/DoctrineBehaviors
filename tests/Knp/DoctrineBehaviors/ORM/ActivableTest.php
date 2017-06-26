<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

require_once 'EntityManagerProvider.php';

class ActivableTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return array(
            'BehaviorFixtures\\ORM\\ActivatableEntity'
        );
    }

    protected function getEventManager()
    {
        $em = new EventManager;

        $em->addEventSubscriber(
            new \Knp\DoctrineBehaviors\ORM\State\ActivatableSubscriber(
                new ClassAnalyzer(),
                true,
                'Knp\DoctrineBehaviors\Model\State\Activatable'
            ));

        return $em;
    }

    /**
     * @test
     */
    public function test_active_set_in_database()
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\ActivatableEntity();

        $entity->setTitle('this is my title');
        $entity->setActive(true);

        $em->persist($entity);
        $em->flush();

        $this->assertEquals(
            $entity->isActive(),
            true,
            'During the creation, the default active field is equal to true'
        );
    }
}