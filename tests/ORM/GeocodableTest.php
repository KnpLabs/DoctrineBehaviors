<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\Common\EventManager;
use Knp\DoctrineBehaviors\Model\Geocodable\Geocodable;
use Knp\DoctrineBehaviors\ORM\Geocodable\GeocodableSubscriber;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\GeocodableEntity;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/EntityManagerProvider.php';

final class GeocodableTest extends TestCase
{
    use EntityManagerProvider;

    /**
     * @var callable
     */
    private $callable;

    protected function setUp(): void
    {
        $entityManager = $this->getDBEngineEntityManager();

        $cities = $this->dataSetCities();

        foreach ($cities as $city) {
            $entity = new GeocodableEntity($city[1][0], $city[1][1]);
            $entity->setTitle($city[0]);
            $entityManager->persist($entity);
        }

        $entityManager->flush();
    }

    /**
     * @dataProvider dataSetCities
     */
    public function testInsertLocation($city, array $location): void
    {
        $entityManager = $this->getDBEngineEntityManager();

        $repository = $entityManager->getRepository(GeocodableEntity::class);

        $entity = $repository->findOneByTitle($city);

        $this->assertLocation($location, $entity->getLocation());
    }

    /**
     * @dataProvider dataSetCities
     */
    public function testUpdateWithEditLocation($city, array $location, array $newLocation): void
    {
        $entityManager = $this->getDBEngineEntityManager();

        $repository = $entityManager->getRepository(GeocodableEntity::class);

        /** @var GeocodableEntity $entity */
        $entity = $repository->findOneByTitle($city);

        $entity->setLocation(new Point($newLocation[0], $newLocation[1]));

        $newTitle = $city . ' - edited';

        $entity->setTitle($newTitle);

        $entityManager->flush();

        /** @var GeocodableEntity $entity */
        $entity = $repository->findOneByTitle($newTitle);

        $this->assertSame($newTitle, $entity->getTitle());

        $this->assertLocation($newLocation, $entity->getLocation());
    }

    /**
     * @dataProvider dataSetCities
     */
    public function testUpdateWithoutEditLocation($city, array $location): void
    {
        $entityManager = $this->getDBEngineEntityManager();

        $repository = $entityManager->getRepository(GeocodableEntity::class);

        /** @var GeocodableEntity $entity */
        $entity = $repository->findOneByTitle($city);

        $entityManager->flush();

        $this->assertLocation($location, $entity->getLocation());
    }

    /**
     * @dataProvider dataSetCities
     */
    public function testUpdateWithoutEditWithGeocodableWatcher($city, array $location, array $newLocation): void
    {
        $this->callable = null;

        $this->testUpdateWithEditLocation($city, $location, $newLocation);
    }

    public function testGetLocation(): void
    {
        $entityManager = $this->getEntityManager();

        $entity = new GeocodableEntity();

        $entityManager->persist($entity);
        $entityManager->flush();

        $this->assertInstanceOf(Point::class, $entity->getLocation());
    }

    /**
     * Geographical info
     * Taken from http://andrew.hedges.name/experiments/haversine, I don't know if it's the same
     * formula Postgresql uses, but it should do the trick.
     *
     * Requisheim <-> Paris         ~384 km,  ~238 miles
     * Requisheim <-> Nantes        ~671 km,  ~417 miles
     * Requisheim <-> New-York     ~6217 km, ~3864 miles
     *
     * @dataProvider dataSetCitiesDistances
     */
    public function testFindByDistance(array $location, $distance, $result, $text = null)
    {
        if (getenv('DB') === 'mysql') {
            $this->markTestSkipped('findByDistance does not work with MYSQL');

            return null;
        }

        $entityManager = $this->getDBEngineEntityManager();

        $repository = $entityManager->getRepository(GeocodableEntity::class);

        $cities = $repository->findByDistance(new Point($location[0], $location[1]), $distance);

        $this->assertCount($result, $cities, $text);
    }

    public function dataSetCities(): array
    {
        return [
            ['New-York', [40.742786, -73.989272], [40.742787, -73.989273]],
            ['Paris', [48.858842, 2.355194], [48.858843, 2.355195]],
            ['Nantes', [47.218635, -1.544266], [47.218636, -1.544267]],
        ];
    }

    /**
     * From 'Reguisheim' (47.896319, 7.352943)
     */
    public function dataSetCitiesDistances(): array
    {
        return [
            [[47.896319, 7.352943], 384000, 0, 'Paris is more than 384 km far from Reguisheim'],
            [[47.896319, 7.352943], 385000, 1, 'Paris is less than 385 km far from Reguisheim'],
            [[47.896319, 7.352943], 672000, 1, 'Nantes is more than 672 km far from Reguisheim'],
            [[47.896319, 7.352943], 673000, 2, 'Paris and Nantes are less than 673 km far from Reguisheim'],
            [[47.896319, 7.352943], 6222000, 2, 'New-York is more than 6222 km far from Reguisheim'],
            [
                [47.896319, 7.352943],
                6223000,
                3,
                'Paris, Nantes and New-York are less than 6223 km far from Reguisheim',
            ],
        ];
    }

    /**
     * @return string[]
     */
    protected function getUsedEntityFixtures(): array
    {
        return [GeocodableEntity::class];
    }

    protected function getEventManager(): EventManager
    {
        $eventManager = new EventManager();

        if ($this->callable === false) {
            $callable = function ($entity) {
                $location = $entity->getLocation();
                if ($location) {
                    return $location;
                }

                return Point::fromArray([
                    'longitude' => 47.7,
                    'latitude' => 7.9,
                ]);
            };
        } else {
            $callable = $this->callable;
        }

        $eventManager->addEventSubscriber(
            new GeocodableSubscriber(new ClassAnalyzer(), false, Geocodable::class, $callable)
        );

        return $eventManager;
    }

    private function assertLocation(array $expected, ?Point $point = null, $message = null): void
    {
        $this->assertInstanceOf(Point::class, $point, $message);

        $this->assertSame($expected[0], $point->getLatitude(), $message);
        $this->assertSame($expected[1], $point->getLongitude(), $message);
    }
}
