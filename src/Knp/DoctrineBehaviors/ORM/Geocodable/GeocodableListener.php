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

use Knp\DoctrineBehaviors\ORM\AbstractListener;

use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Types\Type;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events;

/**
 * GeocodableListener handle Geocodable entites
 * Adds doctrine point type
 */
class GeocodableListener extends AbstractListener
{
    /**
     * @var callable
     */
    private $geolocationCallable;

    /**
     * @constructor
     *
     * @param callable
     */
    public function __construct(ClassAnalyzer $classAnalyzer, callable $geolocationCallable = null)
    {
        parent::__construct($classAnalyzer);
        
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

        if ($this->isEntitySupported($classMetadata->reflClass)) {

            if (!Type::hasType('point')) {
                Type::addType('point', 'Knp\DoctrineBehaviors\DBAL\Types\PointType');
            }

            $em = $eventArgs->getEntityManager();
            $con = $em->getConnection();

            // skip non-postgres platforms
            if (!$con->getDatabasePlatform() instanceof PostgreSqlPlatform) {
                return;
            }

            // skip platforms with registerd stuff
            if ($con->getDatabasePlatform()->hasDoctrineTypeMappingFor('point')) {
                return;
            }

            $con->getDatabasePlatform()->registerDoctrineTypeMapping('point', 'point');

            $em->getConfiguration()->addCustomNumericFunction(
                'DISTANCE', 'Knp\DoctrineBehaviors\ORM\Geocodable\Query\AST\Functions\DistanceFunction'
            );
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    private function updateLocation(LifecycleEventArgs $eventArgs, $override = false)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $entity = $eventArgs->getEntity();

        $classMetadata = $em->getClassMetadata(get_class($entity));
        if ($this->isEntitySupported($classMetadata->reflClass)) {

            $oldValue = $entity->getLocation();
            if (!$oldValue instanceof Point || $override) {
                $entity->setLocation($this->getLocation($entity));

                $uow->propertyChanged($entity, 'location', $oldValue, $entity->getLocation());
                $uow->scheduleExtraUpdate($entity, [
                    'location' => [$oldValue, $entity->getLocation()],
                ]);
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
            return;
        }

        $callable = $this->geolocationCallable;

        return $callable($entity);
    }

    /**
     * Checks if entity supports Geocodable
     *
     * @param  ClassMetadata $classMetadata
     * @return boolean
     */
    private function isEntitySupported(\ReflectionClass $reflClass)
    {
        return $this->getClassAnalyzer()->hasTrait($reflClass, 'Knp\DoctrineBehaviors\Model\Geocodable\Geocodable');
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
