<?php

namespace Knp\DoctrineBehaviors\ORM\Taggable;

use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\DBAL\Platforms;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Doctrine\ORM\Event\LifecycleEventArgs;

class TaggableSubscriber extends AbstractSubscriber
{

    /**
     * @var string
     */
    protected $taggableTrait;

    /**
     * @var string
     */
    protected $tagTrait;

    /**
     * @var mixed
     */
    protected $taggableFetchMode;

    /**
     * @var mixed
     */
    protected $tagFetchMode;

    /**
     * @var string
     */
    protected $tagPostfix;

    /**
     * @param ClassAnalyzer $classAnalyser
     * @param boolean $isRecursive
     * @param string $taggableTrait
     * @param string $tagTrait
     * @param string $taggableFetchMode
     * @param string $tagFetchMode
     * @param string $tagPostfix
     */
    public function __construct(ClassAnalyzer $classAnalyser, $isRecursive, $taggableTrait, $tagTrait, $taggableFetchMode, $tagFetchMode, $tagPostfix)
    {
        parent::__construct($classAnalyser, $isRecursive);
        $this->setTaggableTrait($taggableTrait);
        $this->setTagTrait($tagTrait);
        $this->setTaggableFetchMode($taggableFetchMode);
        $this->setTagFetchMode($tagFetchMode);
        $this->setTagPostfix($tagPostfix);
    }

    /**
     * @return string
     */
    public function getTaggableTrait()
    {
        return $this->taggableTrait;
    }

    /**
     * @param string $taggableTrait
     */
    public function setTaggableTrait($taggableTrait)
    {
        $this->taggableTrait = $taggableTrait;
    }

    /**
     * @return string
     */
    public function getTagTrait()
    {
        return $this->tagTrait;
    }

    /**
     * @param string $tagTrait
     */
    public function setTagTrait($tagTrait)
    {
        $this->tagTrait = $tagTrait;
    }
    /**
     * @return mixed
     */
    public function getTaggableFetchMode()
    {
        return $this->taggableFetchMode;
    }

    /**
     * @param mixed $taggableFetchMode
     */
    public function setTaggableFetchMode($taggableFetchMode)
    {
        $this->taggableFetchMode = $taggableFetchMode;
    }

    /**
     * @return mixed
     */
    public function getTagFetchMode()
    {
        return $this->tagFetchMode;
    }

    /**
     * @param mixed $tagFetchMode
     */
    public function setTagFetchMode($tagFetchMode)
    {
        $this->tagFetchMode = $tagFetchMode;
    }

    /**
     * Returns hash of events, that this subscriber is bound to.
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::prePersist,
        ];
    }

    /**
     * @return string
     */
    public function getTagPostfix()
    {
        return $this->tagPostfix;
    }

    /**
     * @param string $tagPostfix
     */
    public function setTagPostfix($tagPostfix)
    {
        $this->tagPostfix = $tagPostfix;
    }

    /**
     * Adds mapping to the taggable and tags.
     *
     * @param LoadClassMetadataEventArgs $eventArgs The event arguments
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /**
         * @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
         */
        $classMetadata = $eventArgs->getClassMetadata();
        if (null === $classMetadata->reflClass) {
            return;
        }
        if ($this->isTaggable($classMetadata)) {
            $this->mapTaggable($classMetadata);
        }

        if ($this->isTag($classMetadata)) {
            $this->mapTag($classMetadata);
        }
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        /**
         * @var $entity \Knp\DoctrineBehaviors\Model\Taggable\Taggable
         */
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));
        if (!$this->getClassAnalyzer()->hasMethod($classMetadata->reflClass, 'updateCanonicalFields')) {
            return;
        }
        $entity->updateCanonicalFields();
    }

    private function mapTaggable(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('tags')) {
            $classMetadata->mapOneToMany([
                'fieldName' => 'tags',
                'mappedBy' => 'taggable',
                'cascade' => ['persist', 'merge', 'remove'],
                'fetch' => $this->taggableFetchMode,
                'targetEntity' => $classMetadata->name . $this->getTagPostfix(),
                'orphanRemoval' => true
            ]);
        }
    }

    private function mapTag(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('taggable')) {
            $classMetadata->mapManyToOne([
                'fieldName' => 'taggable',
                'inversedBy' => 'tags',
                'fetch' => $this->getTagFetchMode(),
                'joinColumns' => [[
                    'name' => 'taggable_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],],
                'targetEntity' => substr($classMetadata->name, 0, -strlen($this->getTagPostfix())),
            ]);
        }
        $mappings = [
            'name' => [
                'fieldName' => 'name',
                'columnName' => 'name',
                'type' => 'string',
                'nullable' => false,
            ],
            'nameCanonical' => [
                'fieldName' => 'nameCanonical',
                'columnName' => 'name_canonical',
                'type' => 'string',
                'nullable' => true,
            ],
        ];
        foreach($mappings as $mapping) {
            if (!$classMetadata->hasField($mapping['fieldName'])) {
                $classMetadata->mapField($mapping);
            }
        }
    }

    /**
     * Checks if entity is taggable
     *
     * @param ClassMetadata $classMetadata
     * @return boolean
     */
    private function isTaggable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()
            ->hasTrait($classMetadata->reflClass, $this->getTaggableTrait(), $this->isRecursive);
    }

    /**
     * Checks if entity is tag
     *
     * @param ClassMetadata $classMetadata
     * @return boolean
     */
    private function isTag(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()
            ->hasTrait($classMetadata->reflClass, $this->getTagTrait(), $this->isRecursive);
    }

    /**
     * Convert string FETCH mode to required string
     *
     * @param $fetchMode
     * @return int
     */
    private function convertFetchString($fetchMode)
    {
        if (is_int($fetchMode)) {
            return $fetchMode;
        }
        switch ($fetchMode) {
            case 'LAZY':
                return ClassMetadataInfo::FETCH_LAZY;
            case 'EAGER':
                return ClassMetadataInfo::FETCH_EAGER;
            case 'EXTRA_LAZY':
                return ClassMetadataInfo::FETCH_EXTRA_LAZY;
            default:
                return ClassMetadataInfo::FETCH_LAZY;
        }
    }
}
