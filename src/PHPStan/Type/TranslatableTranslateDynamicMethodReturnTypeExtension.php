<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\PHPStan\Type;

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;

final class TranslatableTranslateDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return TranslatableInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return 'translate' === $methodReflection->getName();
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): \PHPStan\Type\Type
    {
        $translationClass = Helper::getTranslationClassFromMethodReflection($methodReflection);

        return new ObjectType($translationClass);
    }
}
