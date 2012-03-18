<?php

namespace Knp\DoctrineBehaviors\ORM\SoftDeletable;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events;

class SoftDeletableListener implements EventSubscriber
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $classMetadata = $em->getClassMetadata(get_class($entity));
            if ($this->isEntitySupported($classMetadata)) {
                $oldValue = $entity->getDeletedAt();
                $entity->delete();
                $em->persist($entity);
                $uow->propertyChanged($entity, 'deletedAt', $oldValue, $entity->getDeletedAt());
                $uow->scheduleExtraUpdate($entity, array(
                    'deletedAt' => array($oldValue, $entity->getDeletedAt())
                ));
            }
        }
    }

    private function isEntitySupported(ClassMetadata $classMetadata)
    {
        return in_array('Knp\DoctrineBehaviors\ORM\SoftDeletable\SoftDeletable', $classMetadata->reflClass->getTraitNames());
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }
}
