<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Geocodable;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use Doctrine\DBAL\Platforms\MySqlPlatform;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\DBAL\Types\PointType;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\ORM\Geocodable\Query\AST\Functions\DistanceFunction;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * GeocodableSubscriber handle Geocodable entites
 * Adds doctrine point type
 */
class GeocodableSubscriber extends AbstractSubscriber
{
    /**
     * @var callable
     */
    private $geolocationCallable;

    private $geocodableTrait;

    /**
     * @param                                                 $isRecursive
     * @param                                                 $geocodableTrait
     * @param callable                                        $geolocationCallable
     */
    public function __construct(
        ClassAnalyzer $classAnalyzer,
        $isRecursive,
        $geocodableTrait,
        ?callable $geolocationCallable = null
    ) {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->geocodableTrait = $geocodableTrait;
        $this->geolocationCallable = $geolocationCallable;
    }

    /**
     * Adds doctrine point type
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if ($classMetadata->reflClass === null) {
            return;
        }

        if ($this->isGeocodable($classMetadata)) {
            if (! Type::hasType('point')) {
                Type::addType('point', PointType::class);
            }

            $entityManager = $loadClassMetadataEventArgs->getEntityManager();
            $connection = $entityManager->getConnection();

            // skip non-postgres platforms
            if (! $connection->getDatabasePlatform() instanceof PostgreSqlPlatform &&
                ! $connection->getDatabasePlatform() instanceof MySqlPlatform
            ) {
                return;
            }

            // skip platforms with registerd stuff
            if (! $connection->getDatabasePlatform()->hasDoctrineTypeMappingFor('point')) {
                $connection->getDatabasePlatform()->registerDoctrineTypeMapping('point', 'point');

                if ($connection->getDatabasePlatform() instanceof PostgreSqlPlatform) {
                    $entityManager->getConfiguration()->addCustomNumericFunction('DISTANCE', DistanceFunction::class);
                }
            }

            $classMetadata->mapField([
                'fieldName' => 'location',
                'type' => 'point',
                'nullable' => true,
            ]);
        }
    }

    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->updateLocation($lifecycleEventArgs, false);
    }

    public function preUpdate(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->updateLocation($lifecycleEventArgs, true);
    }

    /**
     * @return Point the location
     */
    public function getLocation($entity)
    {
        if ($this->geolocationCallable === null) {
            return false;
        }

        $callable = $this->geolocationCallable;

        return $callable($entity);
    }

    public function getSubscribedEvents()
    {
        return [Events::prePersist, Events::preUpdate, Events::loadClassMetadata];
    }

    public function setGeolocationCallable(callable $callable): void
    {
        $this->geolocationCallable = $callable;
    }

    /**
     * Checks if entity is geocodable
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function isGeocodable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->geocodableTrait,
            $this->isRecursive
        );
    }

    private function updateLocation(LifecycleEventArgs $lifecycleEventArgs, $override = false): void
    {
        $entityManager = $lifecycleEventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $entity = $lifecycleEventArgs->getEntity();

        $classMetadata = $entityManager->getClassMetadata(get_class($entity));
        if ($this->isGeocodable($classMetadata)) {
            $oldValue = $entity->getLocation();
            if (! $oldValue instanceof Point || $override) {
                $newLocation = $this->getLocation($entity);

                if ($newLocation !== false) {
                    $entity->setLocation($newLocation);
                }

                $unitOfWork->propertyChanged($entity, 'location', $oldValue, $entity->getLocation());
                $unitOfWork->scheduleExtraUpdate($entity, [
                    'location' => [$oldValue, $entity->getLocation()],
                ]);
            }
        }
    }
}
