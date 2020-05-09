<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\PHPStan\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

final class TranslationTypeHelper
{
    public static function getTranslationClass(Broker $broker, MethodCall $methodCall, Scope $scope): string
    {
        $type = $scope->getType($methodCall->var);
        $translatableClass = $type->getReferencedClasses()[0];

        return $broker
            ->getClass($translatableClass)
            ->getNativeReflection()
            ->getMethod('getTranslationEntityClass')
            ->invoke(null);
    }

    public static function getTranslatableClass(Broker $broker, MethodCall $methodCall, Scope $scope): string
    {
        $type = $scope->getType($methodCall->var);
        $translatableClass = $type->getReferencedClasses()[0];

        return $broker
            ->getClass($translatableClass)
            ->getNativeReflection()
            ->getMethod('getTranslatableEntityClass')
            ->invoke(null);
    }
}
