<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\PHPStan\Type;

use Doctrine\Common\Collections\Collection;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;

final class TranslatableGetTranslationsDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return TranslatableInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return \in_array($methodReflection->getName(), ['getTranslations', 'getNewTranslations'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): \PHPStan\Type\Type
    {
        $translationClass = Helper::getTranslationClassFromMethodReflection($methodReflection);

        return TypeCombinator::intersect(
            new ObjectType(Collection::class),
            new IterableType(new MixedType(), new ObjectType($translationClass))
        );
    }
}
