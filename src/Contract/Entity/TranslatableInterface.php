<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

use Doctrine\Common\Collections\Collection;

/**
 * @template T of TranslationInterface
 */
interface TranslatableInterface
{
    /**
     * @return Collection<string, T>
     */
    public function getTranslations();

    /**
     * @return Collection<string, T>
     */
    public function getNewTranslations(): Collection;

    /**
     * @param T $translation
     */
    public function addTranslation(TranslationInterface $translation): void;

    /**
     * @param T $translation
     */
    public function removeTranslation(TranslationInterface $translation): void;

    /**
     * Returns translation for specific locale (creates new one if doesn't exists). If requested translation doesn't
     * exist, it will first try to fallback default locale If any translation doesn't exist, it will be added to
     * newTranslations collection. In order to persist new translations, call mergeNewTranslations method, before flush
     *
     * @param string $locale The locale (en, ru, fr) | null If null, will try with current locale
     *
     * @return T
     */
    public function translate(?string $locale = null, bool $fallbackToDefault = true): TranslationInterface;

    /**
     * Merges newly created translations into persisted translations.
     */
    public function mergeNewTranslations(): void;

    public function setCurrentLocale(string $locale): void;

    public function getCurrentLocale(): string;

    public function setDefaultLocale(string $locale): void;

    public function getDefaultLocale(): string;

    /**
     * @return class-string<T>
     */
    public static function getTranslationEntityClass(): string;
}
