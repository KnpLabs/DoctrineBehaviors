<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\PHPStan\Type;

use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;

final class TranslationGetTranslatableDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension, BrokerAwareExtension
{
    /** @var Broker */
    private $broker;

    public function setBroker(Broker $broker): void
    {
        $this->broker = $broker;
    }

    public function getClass(): string
    {
        return TranslationInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return 'getTranslatable' === $methodReflection->getName();
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): \PHPStan\Type\Type
    {
        $translatableClass = Helper::getTranslatableClass($this->broker, $methodCall, $scope);

        return new ObjectType($translatableClass);
    }
}
