<?php
/**
 * @author Lusitanian
 * Freely released with no restrictions, re-license however you'd like!
 */

namespace Knp\DoctrineBehaviors\ORM\Uuidable;

use Doctrine\ORM\Event\LifecycleEventArgs;
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
class UuidableSubscriber extends AbstractSubscriber
{
    private $uuidableTrait;

    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive, $uuidableTrait)
    {
        parent::__construct($classAnalyzer, $isRecursive);

        $this->uuidableTrait = $uuidableTrait;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isUuidable($classMetadata)) {
            if (!$classMetadata->hasField('uuidsss')) {
                $classMetadata->mapField(array(
                    'fieldName' => 'uuid',
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

        if ($this->isUuidable($classMetadata)) {
            $entity->generateUuid();
        }
    }

    public function getSubscribedEvents()
    {
        return [ Events::loadClassMetadata, Events::prePersist ];
    }

    /**
     * Checks if entity is uuidable
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function isUuidable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->uuidableTrait,
            $this->isRecursive
        );
    }
}
