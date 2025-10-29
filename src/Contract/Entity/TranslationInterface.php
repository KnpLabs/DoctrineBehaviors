<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

/**
 * @template T of TranslatableInterface
 */
interface TranslationInterface
{
    /**
     * @return class-string<T>
     */
    public static function getTranslatableEntityClass(): string;

    /**
     * @param T $translatable
     */
    public function setTranslatable(TranslatableInterface $translatable): void;

    /**
     * @return T
     */
    public function getTranslatable(): TranslatableInterface;

    public function setLocale(string $locale): void;

    public function getLocale(): string;

    public function isEmpty(): bool;
}
