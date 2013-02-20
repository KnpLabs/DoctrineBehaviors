<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */

namespace Knp\DoctrineBehaviors\ORM\Sluggable;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Knp\DoctrineBehaviors\ORM\AbstractListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Events,
    Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Sluggable listener.
 *
 * Adds mapping to sluggable entities.
 */
class SluggableListener extends AbstractListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isEntitySupported($classMetadata)) {
            if ($classMetadata->reflClass->hasMethod('generateSlug')) {
                // Call the generateSlug function when the entity is persisted initially and when its updated

                $classMetadata->addLifecycleCallback('generateSlug', Events::prePersist);
                $classMetadata->addLifecycleCallback('generateSlug', Events::preUpdate);
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [ Events::loadClassMetadata ];
    }

    /**
     * Checks whether provided entity is supported.
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return Boolean
     */
    protected function isEntitySupported(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, 'Knp\DoctrineBehaviors\Model\Sluggable\Sluggable');
    }
}
