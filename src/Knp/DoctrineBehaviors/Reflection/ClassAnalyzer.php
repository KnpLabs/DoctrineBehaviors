<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Reflection;

use Doctrine\Common\EventSubscriber;

class ClassAnalyzer
{   
    /**
     * Return TRUE if the given object use the given trait, FALSE if not
     * @param ReflectionClass $class
     * @param string $traitName
     * @param boolean $isRecursive
     */
    public function hasTrait(\ReflectionClass $class, $traitName, $isRecursive = false)
    {
        if (in_array($traitName, $class->getTraitNames())) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if ((false === $isRecursive) || (false === $parentClass)) {
            return false;
        }
        
        return $this->hasTrait($parentClass, $traitName, $isRecursive);
    }

    /**
     * Return TRUE if the given object has the given method, FALSE if not
     * @param ReflectionClass $class
     * @param string $methodName
     */
    public function hasMethod(\ReflectionClass $class, $methodName)
    {
        return $class->hasMethod($methodName);
    }

    /**
     * Return TRUE if the given object has the given property, FALSE if not
     * @param ReflectionClass $class
     * @param string $propertyName
     */
    public function hasProperty(\ReflectionClass $class, $propertyName)
    {
        if ($class->hasProperty($propertyName)) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if (false === $parentClass) {
            return false;
        }
        
        return $this->hasProperty($parentClass, $propertyName);
    }

    /**
     * Return TRUE if the given object has the given property, FALSE if not
     * @param ReflectionClass $class
     * @param string $traitName
     * @param string $propertyName
     */
    public function getRealTraitMethodName(\ReflectionClass $class, $traitName, $methodName)
    {
        $aliases = $class->getTraitAliases();
        $methodFullName = sprintf('%s::%s', $traitName, $methodName);

        if (in_array($methodFullName, $aliases)) {

            return array_search($methodFullName, $aliases);
        }

        if ($this->hasMethod($class, $methodName)) {
            
            return $methodName;
        }

        return null;
    }
}