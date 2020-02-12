<?php

namespace Knp\DoctrineBehaviors\PHPStan\Type;

use PHPStan\Reflection\MethodReflection;

final class Helper
{
    public static function getTranslationClassFromMethodReflection(MethodReflection $methodReflection): string
    {
        $translatableReflection       = $methodReflection->getDeclaringClass();
        $translatableNativeReflection = $translatableReflection->getNativeReflection();

        return $translatableNativeReflection->getMethod('getTranslationEntityClass')->invoke(null);
    }

    public static function getTranslatableClassFromMethodReflection(MethodReflection $methodReflection): string
    {
        $translationReflection       = $methodReflection->getDeclaringClass();
        $translationNativeReflection = $translationReflection->getNativeReflection();

        return $translationNativeReflection->getMethod('getTranslatableEntityClass')->invoke(null);
    }
}
