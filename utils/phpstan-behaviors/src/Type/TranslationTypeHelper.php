<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\PHPStan\Type;

use Exception;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

final class TranslationTypeHelper
{
    public static function getTranslationClass(Broker $broker, MethodCall $methodCall, Scope $scope): string
    {
        $type = $scope->getType($methodCall->var);
        $translatableClass = $type->getReferencedClasses()[0];
        $reflection = $broker->getClass($translatableClass)
            ->getNativeReflection();

        if ($reflection->isInterface()) {
            if ($reflection->getName() === TranslatableInterface::class) {
                return TranslationInterface::class;
            }

            throw new Exception(sprintf(
                'Unable to find the Translation class associated to the Translatable class "%s".',
                $reflection->getName()
            ));
        }

        return $reflection
            ->getMethod('getTranslationEntityClass')
            ->invoke(null);
    }

    public static function getTranslatableClass(Broker $broker, MethodCall $methodCall, Scope $scope): string
    {
        $type = $scope->getType($methodCall->var);
        $translationClass = $type->getReferencedClasses()[0];
        $reflection = $broker->getClass($translationClass)
            ->getNativeReflection();

        if ($reflection->isInterface()) {
            if ($reflection->getName() === TranslationInterface::class) {
                return TranslatableInterface::class;
            }

            throw new Exception(sprintf(
                'Unable to find the Translatable class associated to the Translation class "%s".',
                $reflection->getName()
            ));
        }

        return $reflection
            ->getMethod('getTranslatableEntityClass')
            ->invoke(null);
    }
}
