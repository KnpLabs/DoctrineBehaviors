<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\PHPStan\Type;

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\PHPStan\Exception\PHPStanTypeException;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use ReflectionClass;

final class StaticTranslationTypeHelper
{
    public static function getTranslationClass(
        ReflectionProvider $reflectionProvider,
        MethodCall $methodCall,
        Scope $scope
    ): string {
        $type = $scope->getType($methodCall->var);
        /** @var class-string $translatableClass */
        $translatableClass = $type->getReferencedClasses()[0];

        if (! $reflectionProvider->hasClass($translatableClass)) {
            // for some reason, we the reflectin provided cannot locate the class
            $reflectionClass = new ReflectionClass($translatableClass);
        } else {
            $reflectionClass = $reflectionProvider->getClass($translatableClass)
                ->getNativeReflection();
        }

        if ($reflectionClass->isInterface()) {
            if ($reflectionClass->getName() === TranslatableInterface::class || $reflectionClass->implementsInterface(
                TranslatableInterface::class
            )) {
                return TranslationInterface::class;
            }

            $errorMessage = sprintf(
                'Unable to find the Translation class associated to the Translatable class "%s".',
                $reflectionClass->getName()
            );
            throw new PHPStanTypeException($errorMessage);
        }

        return $reflectionClass
            ->getMethod('getTranslationEntityClass')
            ->invoke(null);
    }

    public static function getTranslatableClass(
        ReflectionProvider $reflectionProvider,
        MethodCall $methodCall,
        Scope $scope
    ): string {
        $type = $scope->getType($methodCall->var);
        $translationClass = $type->getReferencedClasses()[0];
        $nativeReflection = $reflectionProvider->getClass($translationClass)
            ->getNativeReflection();

        if ($nativeReflection->isInterface()) {
            if ($nativeReflection->getName() === TranslationInterface::class || $nativeReflection->implementsInterface(
                TranslationInterface::class
            )) {
                return TranslatableInterface::class;
            }

            $errorMessage = sprintf(
                'Unable to find the Translatable class associated to the Translation class "%s".',
                $nativeReflection->getName()
            );
            throw new PHPStanTypeException($errorMessage);
        }

        return $nativeReflection
            ->getMethod('getTranslatableEntityClass')
            ->invoke(null);
    }
}
