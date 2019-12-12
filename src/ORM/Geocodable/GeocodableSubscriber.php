<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Geocodable;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\Contract\Entity\GeocodableInterface;
use Knp\DoctrineBehaviors\Contract\Provider\LocationProviderInterface;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

final class GeocodableSubscriber implements EventSubscriber
{
    /**
     * @var LocationProviderInterface
     */
    private $locationProvider;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(LocationProviderInterface $locationProvider, Connection $connection)
    {
        $this->locationProvider = $locationProvider;
        $this->connection = $connection;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if (! is_a($classMetadata->reflClass->getName(), GeocodableInterface::class, true)) {
            return;
        }

        // fallback for mysql-5, maybe there is a better location?
        if (! $this->connection->getDatabasePlatform()->hasDoctrineTypeMappingFor('point')) {
            $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('point', 'point');
        }

        $classMetadata->mapField([
            'fieldName' => 'location',
            'type' => 'point',
            'nullable' => true,
        ]);
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
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::prePersist, Events::preUpdate, Events::loadClassMetadata];
    }

    private function updateLocation(LifecycleEventArgs $lifecycleEventArgs, $override = false): void
    {
        $entity = $lifecycleEventArgs->getEntity();
        if (! $entity instanceof GeocodableInterface) {
            return;
        }

        $entityManager = $lifecycleEventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $oldValue = $entity->getLocation();
        if (! $oldValue instanceof Point || $override) {
            $newLocation = $this->locationProvider->providePoint();
            if ($newLocation !== null) {
                $entity->setLocation($newLocation);
            }

            $unitOfWork->propertyChanged($entity, 'location', $oldValue, $entity->getLocation());

            $unitOfWork->scheduleExtraUpdate($entity, [
                'location' => [$oldValue, $entity->getLocation()],
            ]);
        }
    }
}
