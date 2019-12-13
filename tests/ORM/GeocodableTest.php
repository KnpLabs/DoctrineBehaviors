<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Iterator;
use Knp\DoctrineBehaviors\Contract\Provider\LocationProviderInterface;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;
use Knp\DoctrineBehaviors\Tests\AbstractBehaviorTestCase;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\GeocodableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Repository\GeocodableRepository;

final class GeocodableTest extends AbstractBehaviorTestCase
{
    /**
     * @var GeocodableRepository
     */
    private $geocodableRepository;

    /**
     * @var LocationProviderInterface
     */
    private $locationProvider;

    /**
     * @var Connection
     */
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locationProvider = static::$container->get(LocationProviderInterface::class);
        $this->connection = static::$container->get('doctrine.dbal.default_connection');

        // load cities to database
        $this->loadEntitiesToDatabase();

        $this->geocodableRepository = $this->entityManager->getRepository(GeocodableEntity::class);
    }

    /**
     * @dataProvider dataSetCities()
     */
    public function testInsertLocation($city, Point $expectedPoint): void
    {
        $entity = $this->geocodableRepository->findOneByTitle($city);
        $this->assertLocation($expectedPoint, $entity->getLocation());
    }

    /**
     * @dataProvider dataSetCities()
     */
    public function testUpdateWithEditLocation($city, Point $expectedPoint, Point $newPoint): void
    {
        $entity = $this->geocodableRepository->findOneByTitle($city);

        $entity->setLocation($newPoint);

        $newTitle = $city . ' - edited';
        $entity->setTitle($newTitle);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $entity = $this->geocodableRepository->findOneByTitle($newTitle);
        $this->assertSame($newTitle, $entity->getTitle());

        $providedPoint = $this->locationProvider->providePoint();
        $this->assertLocation($providedPoint, $entity->getLocation());
    }

    /**
     * @dataProvider dataSetCities()
     */
    public function testUpdateWithoutEditLocation(string $city, Point $expectedPoint): void
    {
        $entity = $this->geocodableRepository->findOneByTitle($city);

        $this->entityManager->flush();

        $this->assertLocation($expectedPoint, $entity->getLocation());
    }

    public function testGetLocation(): void
    {
        $entity = new GeocodableEntity();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

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
     * @dataProvider dataSetCitiesDistances()
     */
    public function testFindByDistance(Point $point, int $distance, $result): void
    {
        // skip non-postgres platforms
        $databasePlatform = $this->connection->getDatabasePlatform();
        if (! $databasePlatform instanceof PostgreSqlPlatform/* && ! $databasePlatform instanceof MySqlPlatform*/) {
            $this->markTestSkipped('Skip non-postgres platforms');
        }

        if (getenv('DB_ENGINE') === 'pdo_mysql') {
            $this->markTestSkipped('findByDistance does not work with MYSQL');
        }

        $cities = $this->geocodableRepository->findByDistance($point, $distance);

        $this->assertCount($result, $cities);
    }

    public function dataSetCities(): Iterator
    {
        $currentPoint = new Point(40.742786, -73.989272);
        $newPoint = new Point(40.742787, -73.989273);
        yield ['New-York', $currentPoint, $newPoint];

        $currentPoint = new Point(48.858842, 2.355194);
        $newPoint = new Point(48.858843, 2.355195);
        yield ['Paris', $currentPoint, $newPoint];

        $currentPoint = new Point(47.218635, -1.544266);
        $newPoint = new Point(47.218636, -1.544267);
        yield ['Nantes', $currentPoint, $newPoint];
    }

    /**
     * From 'Reguisheim' (47.896319, 7.352943)
     */
    public function dataSetCitiesDistances(): Iterator
    {
        $point = new Point(47.896319, 7.352943);

        yield [$point, 384000, 0, 'Paris is more than 384 km far from Reguisheim'];
        yield [$point, 385000, 1, 'Paris is less than 385 km far from Reguisheim'];
        yield [$point, 672000, 1, 'Nantes is more than 672 km far from Reguisheim'];
        yield [$point, 673000, 2, 'Paris and Nantes are less than 673 km far from Reguisheim'];
        yield [$point, 6222000, 2, 'New-York is more than 6222 km far from Reguisheim'];
        yield [$point, 6223000, 3, 'Paris, Nantes and New-York are less than 6223 km far from Reguisheim'];
    }

    private function loadEntitiesToDatabase(): void
    {
        foreach ($this->dataSetCities() as $city) {
            /** @var Point $cityPoint */
            $cityPoint = $city[1];

            $entity = new GeocodableEntity($cityPoint->getLatitude(), $cityPoint->getLongitude());
            $entity->setTitle($city[0]);

            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }

    private function assertLocation(Point $expectedPoint, ?Point $point): void
    {
        $this->assertInstanceOf(Point::class, $point);

        $this->assertSame($expectedPoint->getLatitude(), $point->getLatitude());
        $this->assertSame($expectedPoint->getLongitude(), $point->getLongitude());
    }
}
