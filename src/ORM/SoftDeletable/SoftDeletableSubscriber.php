<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\SoftDeletable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableInterface;

/**
 * SoftDeletable Doctrine2 subscriber.
 *
 * Listens to onFlush event and marks SoftDeletable entities
 * as deleted instead of really removing them.
 */
class SoftDeletableSubscriber implements EventSubscriber
{
    /**
     * Listens to onFlush event.
     *
     * @param OnFlushEventArgs $args The event arguments
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em  = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        /** @var SoftDeletableInterface $entity */
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $classMetadata = $em->getClassMetadata(get_class($entity));
            if (is_subclass_of($classMetadata->getName(), 'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableInterface')) {
                $oldValue = $entity->getDeletedAt();

                $entity->delete();
                $em->persist($entity);

                $uow->propertyChanged($entity, 'deletedAt', $oldValue, $entity->getDeletedAt());
                $uow->scheduleExtraUpdate($entity, [
                    'deletedAt' => [$oldValue, $entity->getDeletedAt()]
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

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if (is_subclass_of($classMetadata->getName(), 'Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletableInterface')) {
            if (!$classMetadata->hasField('deletedAt')) {
                $classMetadata->mapField(array(
                    'fieldName' => 'deletedAt',
                    'type'      => 'datetime',
                    'nullable'  => true,
                ));
            }
        }
    }
}
