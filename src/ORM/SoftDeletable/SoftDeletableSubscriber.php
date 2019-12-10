<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\SoftDeletable;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * Listens to onFlush event and marks SoftDeletable entities as deleted instead of really removing them.
 */
class SoftDeletableSubscriber extends AbstractSubscriber
{
    private $softDeletableTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $softDeletableTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->softDeletableTrait = $softDeletableTrait;
    }

    /**
     * Listens to onFlush event.
     *
     * @param OnFlushEventArgs $onFlushEventArgs The event arguments
     */
    public function onFlush(OnFlushEventArgs $onFlushEventArgs): void
    {
        $entityManager = $onFlushEventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            $classMetadata = $entityManager->getClassMetadata(get_class($entity));
            if ($this->isSoftDeletable($classMetadata)) {
                $oldValue = $entity->getDeletedAt();

                $entity->delete();
                $entityManager->persist($entity);

                $unitOfWork->propertyChanged($entity, 'deletedAt', $oldValue, $entity->getDeletedAt());
                $unitOfWork->scheduleExtraUpdate($entity, [
                    'deletedAt' => [$oldValue, $entity->getDeletedAt()],
                ]);
            }
        }
    }

    /**
     * Returns list of events, that this subscriber is listening to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::onFlush, Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if ($classMetadata->reflClass === null) {
            return;
        }

        if ($this->isSoftDeletable($classMetadata)) {
            if (! $classMetadata->hasField('deletedAt')) {
                $classMetadata->mapField([
                    'fieldName' => 'deletedAt',
                    'type' => 'datetime',
                    'nullable' => true,
                ]);
            }
        }
    }

    /**
     * Checks if entity is softDeletable
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function isSoftDeletable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->softDeletableTrait,
            $this->isRecursive
        );
    }
}
