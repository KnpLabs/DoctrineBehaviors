<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */

namespace Knp\DoctrineBehaviors\ORM\Sluggable;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableInterface;
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
        $entity = $eventArgs->getEntity();
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($entity instanceof SluggableInterface) {
            if (!$classMetadata->hasField('slug')) {
                $classMetadata->mapField(array(
                    'fieldName' => 'slug',
                    'type'      => 'string',
                    'nullable'  => true
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
