<?php

declare(strict_types=1);

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\SoftDeletable;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events,
    Knp\DoctrineBehaviors\ORM\AbstractSubscriber,
    Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * SoftDeletable Doctrine2 subscriber.
 *
 * Listens to onFlush event and marks SoftDeletable entities
 * as deleted instead of really removing them.
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
     * @param OnFlushEventArgs $args The event arguments
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $classMetadata = $em->getClassMetadata(get_class($entity));
            if ($this->isSoftDeletable($classMetadata)) {
                $oldValue = $entity->getDeletedAt();

                $entity->delete();
                $em->persist($entity);

                $uow->propertyChanged($entity, 'deletedAt', $oldValue, $entity->getDeletedAt());
                $uow->scheduleExtraUpdate($entity, [
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

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

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
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, $this->softDeletableTrait, $this->isRecursive);
    }
}
