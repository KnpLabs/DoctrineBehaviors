<?php

namespace Knp\DoctrineBehaviors\ORM\SoftDeletable;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\LifecycleEventArgs,
    Doctrine\ORM\Events;

class SoftDeletableListener implements EventSubscriber
{
    public function preRemove(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $entity = $args->getEntity();

        if (method_exists($entity, 'delete')) {
            $em->detach($entity);
            $em->clear();
            $entity->delete();
            $em->persist($entity);
        }
    }

    public function getSubscribedEvents()
    {
        return array(Events::preRemove);
    }
}
