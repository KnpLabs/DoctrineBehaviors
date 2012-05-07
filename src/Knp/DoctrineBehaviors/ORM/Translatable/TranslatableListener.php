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
class TranslatableListener implements EventSubscriber
{
    private $currentLocaleCallable;

    public function __construct(callable $currentLocaleCallable = null)
    {
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

        if ($this->isTranslatable($classMetadata->reflClass)) {
            $classMetadata->mapOneToMany([
                'fieldName'    => 'translations',
                'mappedBy'     => 'translatable',
                'cascade'      => ['persist', 'merge', 'remove'],
                'targetEntity' => $classMetadata->name.'Translation'
            ]);
        }

        if ($this->isTranslation($classMetadata)) {
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

            $classMetadata->setPrimaryTable([
                'uniqueConstraints' => [[
                    'name'    => $classMetadata->getTableName().'_unique_translation',
                    'columns' => ['translatable_id', 'locale' ]
                ]],
            ]);
        }
    }

    /**
     * Checks if entity is translatable
     *
     * @param ClassMetadata $classMetadata
     * @param bool          $isRecursive    true to check for parent classes until trait is found
     *
     * @return boolean
     */
    private function isTranslatable(\ReflectionClass $reflClass, $isRecursive = false)
    {
        $traitNames = $reflClass->getTraitNames();

        $isSupported = in_array('Knp\DoctrineBehaviors\ORM\Translatable\Translatable', $reflClass->getTraitNames());

        while($isRecursive and !$isSupported and $reflClass->getParentClass()) {
            $reflClass = $reflClass->getParentClass();
            $isSupported = $this->isTranslatable($reflClass, true);
        }

        return $isSupported;
    }

    private function isTranslation(ClassMetadata $classMetadata)
    {
        $traitNames = $classMetadata->reflClass->getTraitNames();

        return in_array('Knp\DoctrineBehaviors\ORM\Translatable\Translation', $traitNames);
    }

    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $em            = $eventArgs->getEntityManager();
        $entity        = $eventArgs->getEntity();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if (!$this->isTranslatable($classMetadata->reflClass, true)) {
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
