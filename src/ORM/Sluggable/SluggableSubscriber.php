<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */

namespace Knp\DoctrineBehaviors\ORM\Sluggable;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableInterface;

/**
 * Sluggable subscriber.
 *
 * Adds mapping to sluggable entities.
 */
class SluggableSubscriber implements EventSubscriber
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if (is_subclass_of($classMetadata->getName(), 'Knp\DoctrineBehaviors\Model\Sluggable\SluggableInterface')) {
            if (!$classMetadata->hasField('slug')) {
                $classMetadata->mapField(array(
                    'fieldName' => 'slug',
                    'type'      => 'string',
                    'nullable'  => true,
                ));
            }
        }
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof SluggableInterface) {
            $entity->generateSlug();
        }
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof SluggableInterface) {
            $entity->generateSlug();
        }
    }

    public function getSubscribedEvents()
    {
        return [ Events::loadClassMetadata, Events::prePersist, Events::preUpdate ];
    }
}
