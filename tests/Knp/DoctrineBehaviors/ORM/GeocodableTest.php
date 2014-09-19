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

        $em->addEventSubscriber(new \Knp\DoctrineBehaviors\ORM\Geocodable\GeocodableSubscriber(
            new ClassAnalyzer(),
            false,
            'Knp\DoctrineBehaviors\Model\Geocodable\Geocodable',
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

    public function setUp()
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
    }

    public function testGetLocation()
    {
        $em = $this->getEntityManager();

        $entity = new \BehaviorFixtures\ORM\GeocodableEntity();

        $em->persist($entity);
        $em->flush();

        $this->assertInstanceOf('Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point', $entity->getLocation());
    }

    /**
     * Geographical info
     * Taken from http://andrew.hedges.name/experiments/haversine, I don't know if it's the same
     * formula Postgresql uses, but it should do the trick.
     *
     * Requisheim <-> Paris         ~384 km,  ~238 miles
     * Requisheim <-> Nantes        ~671 km,  ~417 miles
     * Requisheim <-> New-York     ~6217 km, ~3864 miles
     */
    public function testFindByDistance()
    {
        $em = $this->getEntityManager(null, null, [
            'driver' => 'pdo_pgsql',
            'dbname' => 'orm_behaviors_test',
        ]);

        $repo = $em->getRepository('BehaviorFixtures\ORM\GeocodableEntity');

        $cities = $repo->findByDistance(new Point(47.896319, 7.352943), 384000);
        $this->assertCount(0, $cities, 'Paris is more than 384 km far from Reguisheim');

        $cities = $repo->findByDistance(new Point(47.896319, 7.352943), 385000);
        $this->assertCount(1, $cities, 'Paris is less than 385 km far from Reguisheim');

        $cities = $repo->findByDistance(new Point(47.896319, 7.352943), 672000);
        $this->assertCount(1, $cities, 'Nantes is more than 672 km far from Reguisheim');

        $cities = $repo->findByDistance(new Point(47.896319, 7.352943), 673000);
        $this->assertCount(2, $cities, 'Paris and Nantes are less than 673 km far from Reguisheim');

        $cities = $repo->findByDistance(new Point(47.896319, 7.352943), 6222000);
        $this->assertCount(2, $cities, 'New-York is more than 6222 km far from Reguisheim');

        $cities = $repo->findByDistance(new Point(47.896319, 7.352943), 6223000);
        $this->assertCount(3, $cities, 'Paris, Nantes and New-York are less than 6223 km far from Reguisheim');
    }
}
