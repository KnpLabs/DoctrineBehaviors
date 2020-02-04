<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

/**
 * @phpstan-template T of TranslatableInterface
 */
interface TranslationInterface
{
    public static function getTranslatableEntityClass(): string;

    /**
     * @phpstan-param T $translatable
     */
    public function setTranslatable(TranslatableInterface $translatable): void;

    /**
     * @phpstan-return T
     */
    public function getTranslatable(): TranslatableInterface;

    public function setLocale(string $locale): void;

    public function getLocale(): string;

    public function isEmpty(): bool;
}
