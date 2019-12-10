<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\SoftDeletable;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

final class SoftDeletableSubscriber extends AbstractSubscriber
{
    /**
     * @var string
     */
    private $softDeletableTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, bool $isRecursive, string $softDeletableTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->softDeletableTrait = $softDeletableTrait;
    }

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
     * @return string[]
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

        if (! $this->isSoftDeletable($classMetadata)) {
            return;
        }

        if ($classMetadata->hasField('deletedAt')) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => 'deletedAt',
            'type' => 'datetime',
            'nullable' => true,
        ]);
    }

    private function isSoftDeletable(ClassMetadata $classMetadata): bool
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->softDeletableTrait,
            $this->isRecursive
        );
    }
}
