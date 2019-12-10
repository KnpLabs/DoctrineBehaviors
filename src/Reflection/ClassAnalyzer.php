<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Reflection;

use ReflectionClass;

class ClassAnalyzer
{
    /**
     * Return TRUE if the given object use the given trait, FALSE if not
     * @param string $traitName
     * @param boolean $isRecursive
     */
    public function hasTrait(ReflectionClass $reflectionClass, $traitName, $isRecursive = false)
    {
        if (in_array($traitName, $reflectionClass->getTraitNames(), true)) {
            return true;
        }

        $parentClass = $reflectionClass->getParentClass();

        if (($isRecursive === false) || ($parentClass === false) || ($parentClass === null)) {
            return false;
        }

        return $this->hasTrait($parentClass, $traitName, $isRecursive);
    }

    /**
     * Return TRUE if the given object has the given method, FALSE if not
     * @param string $methodName
     */
    public function hasMethod(ReflectionClass $reflectionClass, $methodName)
    {
        return $reflectionClass->hasMethod($methodName);
    }

    /**
     * Return TRUE if the given object has the given property, FALSE if not
     * @param string $propertyName
     */
    public function hasProperty(ReflectionClass $reflectionClass, $propertyName)
    {
        if ($reflectionClass->hasProperty($propertyName)) {
            return true;
        }

        $parentClass = $reflectionClass->getParentClass();

        if ($parentClass === false) {
            return false;
        }

        return $this->hasProperty($parentClass, $propertyName);
    }
}
