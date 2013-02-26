<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultGeocodableTest.php';

class RenamedGeocodableTest extends DefaultGeocodableTest
{
    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedGeocodableEntity";
    }

    public function testGetLocation()
    {
        $em = $this->getEntityManager();

        $entity = $this->getTestedEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertInstanceOf('Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point', $entity->getTraitLocation());
    }
}
