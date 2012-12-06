<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventSubscriber;

abstract class AbstractListener implements EventSubscriber
{

    abstract public function getSubscribedEvents();
    
    /**
     * Return TRUE if the given entity use the given trait, FALSE if not
     * @param ReflectionClass $class
     * @param string $traitName
     * @param boolean $isRecursive
     */
    protected function isEntityUseTrait(\ReflectionClass $class, $traitName, $isRecursive = false)
    {
        if (in_array($traitName, $class->getTraitNames())) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if ((false === $isRecursive) || (false === $parentClass)) {
            return false;
        }
        
        return $this->isEntityUseTrait($parentClass, $traitName, $isRecursive);
    }

    /**
     * Return TRUE if the given entity has the given method, FALSE if not
     * @param ReflectionClass $class
     * @param string $methodName
     */
    protected function isEntityHasMethod(\ReflectionClass $class, $methodName)
    {
        return $class->hasMethod($methodName);
    }

    /**
     * Return TRUE if the given entity has the given property, FALSE if not
     * @param ReflectionClass $class
     * @param string $propertyName
     */
    protected function isEntityHasProperty(\ReflectionClass $class, $propertyName, $isRecursive = false)
    {
        if ($class->hasProperty($propertyName)) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if ((false === $isRecursive) || (false === $parentClass)) {
            return false;
        }
        
        return $this->isEntityHasProperty($parentClass, $propertyName, $isRecursive);
    }
}