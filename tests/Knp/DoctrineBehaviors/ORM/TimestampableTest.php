<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventManager;

require_once 'EntityManagerProvider.php';

class TimestampableTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return array(
            'BehaviorFixtures\\ORM\\TimestampableEntity'
        );
    }

    public function testCreate()
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\TimestampableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertNotNull($id = $entity->getId());

        var_dump($entity);
    }
}
