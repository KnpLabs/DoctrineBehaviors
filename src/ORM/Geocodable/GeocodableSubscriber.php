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

final class GeocodableSubscriber extends AbstractSubscriber
{
    /**
     * @var callable
     */
    private $geolocationCallable;

    /**
     * @var string
     */
    private $geocodableTrait;

    public function __construct(
        ClassAnalyzer $classAnalyzer,
        bool $isRecursive,
        string $geocodableTrait,
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

    public function getLocation(Point $point)
    {
        if ($this->geolocationCallable === null) {
            return false;
        }

        $callable = $this->geolocationCallable;
        return $callable($point);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [Events::prePersist, Events::preUpdate, Events::loadClassMetadata];
    }

    public function setGeolocationCallable(callable $callable): void
    {
        $this->geolocationCallable = $callable;
    }

    private function isGeocodable(ClassMetadata $classMetadata): bool
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
