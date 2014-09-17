<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */

namespace Knp\DoctrineBehaviors\ORM\Sluggable;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Events,
    Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Sluggable subscriber.
 *
 * Adds mapping to sluggable entities.
 */
class SluggableSubscriber extends AbstractSubscriber
{
    private $sluggableTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $sluggableTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->sluggableTrait = $sluggableTrait;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isSluggable($classMetadata)) {
            if ($classMetadata->reflClass->hasMethod('generateSlug')) {
                // Call the generateSlug function when the entity is persisted initially and when its updated

                $classMetadata->addLifecycleCallback('generateSlug', Events::prePersist);
                $classMetadata->addLifecycleCallback('generateSlug', Events::preUpdate);
            }

            if (!$classMetadata->hasField('slug')) {
                $classMetadata->mapField(array(
                    'fieldName' => 'slug',
                    'type'      => 'string',
                    'nullable'  => true
                ));
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [ Events::loadClassMetadata ];
    }

    /**
     * Checks if entity is sluggable
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function isSluggable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->sluggableTrait,
            $this->isRecursive
        );
    }
}
