<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

use Doctrine\Common\Collections\Collection;

interface TranslatableInterface
{
    /**
     * @return Collection|TranslationInterface[]
     */
    public function getTranslations();

    /**
     * @return Collection|TranslationInterface[]
     */
    public function getNewTranslations();

    public function addTranslation(TranslationInterface $translation): void;

    public function removeTranslation(TranslationInterface $translation): void;

    /**
     * Returns translation for specific locale (creates new one if doesn't exists).
     * If requested translation doesn't exist, it will first try to fallback default locale
     * If any translation doesn't exist, it will be added to newTranslations collection.
     * In order to persist new translations, call mergeNewTranslations method, before flush
     *
     * @param string $locale The locale (en, ru, fr) | null If null, will try with current locale
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

    public static function getTranslationEntityClass(): string;
}
