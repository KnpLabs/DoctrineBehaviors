<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface;

#[AsDoctrineListener(event: Events::loadClassMetadata)]
#[AsDoctrineListener(event: Events::onFlush)]
final class SoftDeletableEventSubscriber
{
    /**
     * @var string
     */
    private const DELETED_AT = 'deletedAt';

    public function onFlush(OnFlushEventArgs $onFlushEventArgs): void
    {
        $entityManager = $onFlushEventArgs->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            if (! $entity instanceof SoftDeletableInterface) {
                continue;
            }

            $oldValue = $entity->getDeletedAt();

            $entity->delete();
            $entityManager->persist($entity);

            $unitOfWork->propertyChanged($entity, self::DELETED_AT, $oldValue, $entity->getDeletedAt());
            $unitOfWork->scheduleExtraUpdate($entity, [
                self::DELETED_AT => [$oldValue, $entity->getDeletedAt()],
            ]);
        }
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if (! is_a($classMetadata->reflClass->getName(), SoftDeletableInterface::class, true)) {
            return;
        }

        if ($classMetadata->hasField(self::DELETED_AT)) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => self::DELETED_AT,
            'type' => 'datetime',
            'nullable' => true,
        ]);
    }
}
