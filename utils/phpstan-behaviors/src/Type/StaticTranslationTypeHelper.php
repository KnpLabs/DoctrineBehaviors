<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\PHPStan\Type;

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Throwable;

final class StaticTranslationTypeHelper
{
    /**
     * Resolves the translation type for a call on a TranslatableInterface object.
     * Returns null when nothing can be resolved, so PHPStan falls back to the declared return type.
     */
    public static function getTranslationType(
        ReflectionProvider $reflectionProvider,
        MethodCall $methodCall,
        Scope $scope
    ): ?Type {
        return self::resolveRelatedType(
            $reflectionProvider,
            $scope->getType($methodCall->var),
            TranslatableInterface::class,
            'getTranslationEntityClass',
            TranslationInterface::class
        );
    }

    /**
     * Resolves the translatable type for a call on a TranslationInterface object.
     * Returns null when nothing can be resolved, so PHPStan falls back to the declared return type.
     */
    public static function getTranslatableType(
        ReflectionProvider $reflectionProvider,
        MethodCall $methodCall,
        Scope $scope
    ): ?Type {
        return self::resolveRelatedType(
            $reflectionProvider,
            $scope->getType($methodCall->var),
            TranslationInterface::class,
            'getTranslatableEntityClass',
            TranslatableInterface::class
        );
    }

    /**
     * @param class-string $contractInterface interface the called-on class must implement
     * @param class-string $fallbackInterface type used when the related class cannot be determined
     */
    private static function resolveRelatedType(
        ReflectionProvider $reflectionProvider,
        Type $calledOnType,
        string $contractInterface,
        string $relatedClassMethod,
        string $fallbackInterface
    ): ?Type {
        $relatedTypes = [];

        // a union or intersection type can contain several classes, e.g. "TranslatableInterface&OtherInterface"
        foreach ($calledOnType->getObjectClassNames() as $className) {
            if (! $reflectionProvider->hasClass($className)) {
                continue;
            }

            $classReflection = $reflectionProvider->getClass($className);
            if ($classReflection->getName() !== $contractInterface && ! $classReflection->implementsInterface(
                $contractInterface
            )) {
                // not part of the translatable contract, e.g. the other side of an intersection type
                continue;
            }

            $relatedTypes[] = self::resolveRelatedClassType(
                $reflectionProvider,
                $classReflection->isInterface() ? null : $className,
                $relatedClassMethod,
                $fallbackInterface
            );
        }

        if ($relatedTypes === []) {
            return null;
        }

        return TypeCombinator::union(...$relatedTypes);
    }

    private static function resolveRelatedClassType(
        ReflectionProvider $reflectionProvider,
        ?string $className,
        string $relatedClassMethod,
        string $fallbackInterface
    ): Type {
        if ($className === null) {
            return new ObjectType($fallbackInterface);
        }

        try {
            $relatedClass = $reflectionProvider->getClass($className)
                ->getNativeReflection()
                ->getMethod($relatedClassMethod)
                ->invoke(null);
        } catch (Throwable) {
            // e.g. the class is known to PHPStan but not autoloadable, so its static method cannot be invoked
            return new ObjectType($fallbackInterface);
        }

        if (! is_string($relatedClass) || ! $reflectionProvider->hasClass($relatedClass)) {
            // e.g. an abstract class whose "<ClassName>Translation" does not exist
            return new ObjectType($fallbackInterface);
        }

        return new ObjectType($relatedClass);
    }
}
