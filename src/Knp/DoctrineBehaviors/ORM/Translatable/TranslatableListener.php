<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

use Knp\DoctrineBehaviors\ORM\AbstractListener;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Event\LoadClassMetadataEventArgs,
    Doctrine\ORM\Event\LifecycleEventArgs,
    Doctrine\ORM\Events;

/**
 * Translatable Doctrine2 listener.
 *
 * Provides mapping for translatable entities and their translations.
 */
class TranslatableListener extends AbstractListener
{
    private $currentLocaleCallable;

    public function __construct(ClassAnalyzer $classAnalyzer, callable $currentLocaleCallable = null)
    {
        parent::__construct($classAnalyzer);
        
        $this->currentLocaleCallable = $currentLocaleCallable;
    }

    /**
     * Adds mapping to the translatable and translations.
     *
     * @param LoadClassMetadataEventArgs $eventArgs The event arguments
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (null === $classMetadata->reflClass) {
            return;
        }

        if ($this->isTranslatable($classMetadata)) {
            $this->mapTranslatable($classMetadata);
        }

        if ($this->isTranslation($classMetadata)) {
            $this->mapTranslation($classMetadata);
        }
    }

    private function mapTranslatable(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('translations')) {
            $classMetadata->mapOneToMany([
                'fieldName'     => 'translations',
                'mappedBy'      => 'translatable',
                'indexBy'       => 'locale',
                'cascade'       => ['persist', 'merge', 'remove'],
                'targetEntity'  => $classMetadata->name.'Translation',
                'orphanRemoval' => true
            ]);
        }
    }

    private function mapTranslation(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('translatable')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'translatable',
                'inversedBy'   => 'translations',
                'joinColumns'  => [[
                    'name'                 => 'translatable_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'CASCADE'
                ]],
                'targetEntity' => substr($classMetadata->name, 0, -11)
            ]);
        }

        $name = $classMetadata->getTableName().'_unique_translation';
        if (!$this->hasUniqueTranslationConstraint($classMetadata, $name)) {
            $classMetadata->setPrimaryTable([
                'uniqueConstraints' => [[
                    'name'    => $name,
                    'columns' => ['translatable_id', 'locale' ]
                ]],
            ]);
        }
    }

    private function hasUniqueTranslationConstraint(ClassMetadata $classMetadata, $name)
    {
        if (!isset($classMetadata->table['uniqueConstraints'])) {
            return;
        }

        $constraints = array_filter($classMetadata->table['uniqueConstraints'], function($constraint) use ($name) {
            return $name === $constraint['name'];
        });

        return 0 !== count($constraints);
    }

    /**
     * Checks if entity is translatable
     *
     * @param ClassMetadata $classMetadata
     * @param bool          $isRecursive   true to check for parent classes until found
     *
     * @return boolean
     */
    private function isTranslatable(ClassMetadata $classMetadata, $isRecursive = false)
    {
        return $this->getClassAnalyzer()->hasProperty($classMetadata->reflClass, 'translations', $isRecursive);
    }

    private function isTranslation(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasProperty($classMetadata->reflClass, 'translatable');
    }

    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if (!$this->getClassAnalyzer()->hasMethod($classMetadata->reflClass, 'setCurrentLocale', false)) {
            return;
        }

        if ($locale = $this->getCurrentLocale()) {
            $entity->setCurrentLocale($locale);
        }
    }

    private function getCurrentLocale()
    {
        if ($currentLocaleCallable = $this->currentLocaleCallable) {
            return $currentLocaleCallable();
        }
    }

    /**
     * Returns hash of events, that this listener is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
            Events::postLoad,
        ];
    }
}
