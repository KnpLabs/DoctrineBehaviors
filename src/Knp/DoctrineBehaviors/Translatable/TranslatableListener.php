<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Translatable;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Translatable Doctrine2 listener.
 *
 * Provides mapping for translatable entities and their translations.
 */
abstract class TranslatableListener implements EventSubscriber
{
    protected $currentLocaleCallable;

    abstract protected function mapTranslatable(ClassMetadata $classMetadata);

    abstract protected function mapTranslation(ClassMetadata $classMetadata);

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
            $this->mapTranslatable($classMetadata);
        }

        if ($this->isTranslation($classMetadata)) {
            $this->mapTranslation($classMetadata);
        }
    }

    /**
     * Checks if entity is translatable
     *
     * @param ClassMetadata $classMetadata
     * @param bool          $isRecursive   true to check for parent classes until found
     *
     * @return boolean
     */
    private function isTranslatable(\ReflectionClass $reflClass, $isRecursive = false)
    {
        $isSupported = $reflClass->hasProperty('translations');

        while ($isRecursive and !$isSupported and $reflClass->getParentClass()) {
            $reflClass = $reflClass->getParentClass();
            $isSupported = $this->isTranslatable($reflClass, true);
        }

        return $isSupported;
    }

    private function isTranslation(ClassMetadata $classMetadata)
    {
        return $classMetadata->reflClass->hasProperty('translatable');
    }

    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $om            = $eventArgs->getObjectManager();
        $object        = $eventArgs->getObject();
        $classMetadata = $om->getClassMetadata(get_class($object));

        if (!$classMetadata->reflClass->hasMethod('setCurrentLocale')) {
            return;
        }

        if ($locale = $this->getCurrentLocale()) {
            $object->setCurrentLocale($locale);
        }
    }

    private function getCurrentLocale()
    {
        if ($currentLocaleCallable = $this->currentLocaleCallable) {
            return $currentLocaleCallable();
        }
    }
}
