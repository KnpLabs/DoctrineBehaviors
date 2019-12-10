<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Reflection;

use ReflectionClass;

final class ClassAnalyzer
{
    /**
     * Return TRUE if the given object use the given trait, FALSE if not
     */
    public function hasTrait(ReflectionClass $reflectionClass, string $traitName, bool $isRecursive = false)
    {
        if (in_array($traitName, $reflectionClass->getTraitNames(), true)) {
            return true;
        }

        $parentClass = $reflectionClass->getParentClass();

        if ($isRecursive === false) {
            return false;
        }

        if ($parentClass === false) {
            return false;
        }

        if ($parentClass === null) {
            return false;
        }

        return $this->hasTrait($parentClass, $traitName, $isRecursive);
    }

    public function hasMethod(ReflectionClass $reflectionClass, string $methodName): bool
    {
        return $reflectionClass->hasMethod($methodName);
    }

    public function hasProperty(ReflectionClass $reflectionClass, string $propertyName): bool
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
