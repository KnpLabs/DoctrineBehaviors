<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\PHPStan\Type;

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class TranslatableTranslateDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getClass(): string
    {
        return TranslatableInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'translate';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $translationClass = StaticTranslationTypeHelper::getTranslationClass(
            $this->reflectionProvider,
            $methodCall,
            $scope
        );

        return new ObjectType($translationClass);
    }
}
