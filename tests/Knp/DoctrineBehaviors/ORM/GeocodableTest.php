<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;
use Doctrine\Common\EventManager;

require_once 'EntityManagerProvider.php';

class GeocodableTest extends \PHPUnit_Framework_TestCase
{
    use EntityManagerProvider;

    protected function getUsedEntityFixtures()
    {
        return array(
            'BehaviorFixtures\\ORM\\GeocodableEntity'
        );
    }

    protected function getEventManager()
    {
        $em = new EventManager;

        $em->addEventSubscriber(new \Knp\DoctrineBehaviors\ORM\Geocodable\GeocodableListener(
            function() {
                return Point::fromArray([
                    'longitude' => 47.7,
                    'latitude'  => 7.9
                ]);
            }
        ));

        return $em;
    }

    public function testGetLocation()
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\GeocodableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertInstanceOf('Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point', $entity->getLocation());
    }

    public function testFindByDistance()
    {
        $em = $this->getEntityManager(null, null, [
            'driver' => 'pdo_pgsql',
            'dbname' => 'orm_behaviors_test',
        ]);

        $nantes = new \BehaviorFixtures\ORM\GeocodableEntity();
        $paris = new \BehaviorFixtures\ORM\GeocodableEntity();
        $newYork = new \BehaviorFixtures\ORM\GeocodableEntity();

        $em->persist($nantes);
        $em->persist($paris);
        $em->persist($newYork);
        $em->flush();

        $repo = $em->getRepository('BehaviorFixtures\ORM\GeocodableEntity');
        $cities = $repo->findByDistance($paris->getLocation(), 143);
    }
}
