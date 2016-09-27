<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Geocodable;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;

use Knp\DoctrineBehaviors\Model\Geocodable\GeocodableInterface;

use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events;

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

    private $geocodableTraits;

    /**
     * @param \Knp\DoctrineBehaviors\Reflection\ClassAnalyzer $classAnalyzer
     * @param                                                 $isRecursive
     * @param                                                 $geocodableTrait
     * @param callable                                        $geolocationCallable
     */
    public function __construct(
        ClassAnalyzer $classAnalyzer, $geocodableTraits,
        callable $geolocationCallable = null
    ) {
        parent::__construct($classAnalyzer, false);

        $this->geocodableTraits    = is_array($geocodableTraits) ? $geocodableTraits : array($geocodableTraits);
        $this->geolocationCallable = $geolocationCallable;
    }

    /**
     * Adds doctrine point type
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->requiresGeocodableMapping($classMetadata)) {
            if (!Type::hasType('point')) {
                Type::addType('point', 'Knp\DoctrineBehaviors\DBAL\Types\PointType');
            }

            $em = $eventArgs->getEntityManager();
            $con = $em->getConnection();

            // skip non-postgres platforms
            if (!$con->getDatabasePlatform() instanceof PostgreSqlPlatform &&
                !$con->getDatabasePlatform() instanceof MySqlPlatform
            ) {
                return;
            }

            // skip platforms with registerd stuff
            if (!$con->getDatabasePlatform()->hasDoctrineTypeMappingFor('point')) {

                $con->getDatabasePlatform()->registerDoctrineTypeMapping('point', 'point');

                if ($con->getDatabasePlatform() instanceof PostgreSqlPlatform) {
                    $em->getConfiguration()->addCustomNumericFunction(
                        'DISTANCE',
                        'Knp\DoctrineBehaviors\ORM\Geocodable\Query\AST\Functions\DistanceFunction'
                    );
                }
            }

            $classMetadata->mapField(
                [
                    'fieldName' => 'location',
                    'type'      => 'point',
                    'nullable'  => true
                ]
            );
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    private function updateLocation(LifecycleEventArgs $eventArgs, $override = false)
    {
        $uow = $eventArgs->getEntityManager()->getUnitOfWork();
        $entity = $eventArgs->getEntity();

        if ($entity instanceof GeocodableInterface) {

            $oldValue = $entity->getLocation();
            if (!$oldValue instanceof Point || $override) {
                $newLocation = $this->getLocation($entity);

                if ($newLocation !== false) {
                    $entity->setLocation($newLocation);
                }

                $uow->propertyChanged($entity, 'location', $oldValue, $entity->getLocation());
                $uow->scheduleExtraUpdate(
                    $entity,
                    [
                        'location' => [$oldValue, $entity->getLocation()],
                    ]
                );
            }
        }
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->updateLocation($eventArgs, false);
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->updateLocation($eventArgs, true);
    }

    /**
     * @return Point the location
     */
    public function getLocation($entity)
    {
        if (null === $this->geolocationCallable) {
            return false;
        }

        $callable = $this->geolocationCallable;

        return $callable($entity);
    }

    /**
     * Checks if entity is geocodable
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function requiresGeocodableMapping(ClassMetadata $classMetadata)
    {
        foreach ($this->geocodableTraits as $trait) {
            if ($this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, $trait)) {
                return true;
            }
        }

        return false;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::loadClassMetadata,
        ];
    }

    public function setGeolocationCallable(callable $callable)
    {
        $this->geolocationCallable = $callable;
    }
}
