<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */
namespace Knp\DoctrineBehaviors\ORM\Sluggable;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

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
            if (!$classMetadata->hasField('slug')) {
                $classMetadata->mapField(array(
                    'fieldName' => 'slug',
                    'type' => 'string',
                    'nullable' => true,
                ));
            }
        }
    }

    public function preFlush(PreFlushEventArgs $eventArgs)
    {
        $manager = $eventArgs->getEntityManager();
        $unitOfWork = $manager->getUnitOfWork();

        $scheduledInsertions = $unitOfWork->getScheduledEntityInsertions();

        foreach ($scheduledInsertions as $entity) {
            $classMetadata = $manager->getClassMetadata(get_class($entity));

            if ($this->isSluggable($classMetadata)) {
                $entity->generateSlug();
            }
        }
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $manager = $eventArgs->getEntityManager();
        $unitOfWork = $manager->getUnitOfWork();

        $scheduledUpdates = $unitOfWork->getScheduledEntityUpdates();

        foreach ($scheduledUpdates as $entity) {
            $classMetadata = $manager->getClassMetadata(get_class($entity));

            if ($this->isSluggable($classMetadata)) {
                $entity->generateSlug();
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata, Events::preFlush, Events::onFlush];
    }

    /**
     * Checks if entity is sluggable.
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return bool
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
