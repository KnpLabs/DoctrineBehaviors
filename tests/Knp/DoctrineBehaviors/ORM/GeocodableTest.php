<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
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
            new ClassAnalyzer(),
            function($entity) {
                if ($location = $entity->getLocation()) {
                    return $location;
                }

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

        $nantes  = new \BehaviorFixtures\ORM\GeocodableEntity(47.218635,  -1.544266);
        $nantes->setTitle('Nantes');
        $paris   = new \BehaviorFixtures\ORM\GeocodableEntity(48.858842,   2.355194);
        $paris->setTitle('Paris');
        $newYork = new \BehaviorFixtures\ORM\GeocodableEntity(40.742786, -73.989272);
        $newYork->setTitle('New-York');

        $em->persist($nantes);
        $em->persist($paris);
        $em->persist($newYork);
        $em->flush();

        $repo = $em->getRepository('BehaviorFixtures\ORM\GeocodableEntity');

        $cities = $repo->findByDistance(new Point(47.896319, 7.352943), 500);
        $this->assertCount(1, $cities, 'Paris is less than 500km far from Reguisheim');

        $cities = $repo->findByDistance(new Point(47.896319, 7.352943), 900);
        $this->assertCount(2, $cities, 'Paris and Nantes are less than 900km far from Reguisheim');

        $cities = $repo->findByDistance(new Point(47.896319, 7.352943), 9000);
        $this->assertCount(3, $cities, 'Paris, Nantes and New-York are less than 9000km far from Reguisheim');
    }
}
