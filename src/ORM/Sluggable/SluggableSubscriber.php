<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */

namespace Knp\DoctrineBehaviors\ORM\Sluggable;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\ORM\Events,
    Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\Repository\SluggableRepository;

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
                    'type'      => 'string',
                    'nullable'  => true
                ));
            }
        }
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isSluggable($classMetadata)) {
            $entity->generateSlug();

            if ($entity->shouldGenerateUniqueSlugs()) {
                $this->generateUniqueSlugFor($entity, $em);
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $em = $eventArgs->getEntityManager();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isSluggable($classMetadata)) {
            $entity->generateSlug();

            if ($entity->shouldGenerateUniqueSlugs()) {
                $this->generateUniqueSlugFor($entity, $em, $classMetadata);
            }
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

    /**
     * @param $entity
     * @param EntityManager $em
     * @param ClassMetadata $classMetadata
     */
    private function generateUniqueSlugFor($entity, EntityManager $em, ClassMetadata $classMetadata)
    {
        $repo = $em->getRepository($classMetadata->name);
        if (!$repo instanceof SluggableRepository) {
            // Create default repository if entity one do not extends it
            $repo = new SluggableRepository($em, $classMetadata);
        }

        $i = 0;
        $slug = $entity->getSlug();
        $uniqueSlug = $slug;
        while ((bool)$repo->isSlugUniqueFor($entity, $uniqueSlug)) {
            $uniqueSlug = $slug . '-' . ++$i;
        }

        $entity->setSlug($uniqueSlug);
    }
}
