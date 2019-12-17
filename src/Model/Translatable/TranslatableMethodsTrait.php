<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;

trait TranslatableMethodsTrait
{
    /**
     * @return Collection|TranslationInterface[]
     */
    public function getTranslations()
    {
        // initialize collection, usually in ctor
        if ($this->translations === null) {
            $this->translations = new ArrayCollection();
        }

        return $this->translations;
    }

    /**
     * @param Collection|TranslationInterface[] $translations
     */
    public function setTranslations(iterable $translations): void
    {
        $this->ensureIsIterableOrCollection($translations);

        foreach ($translations as $translation) {
            $this->addTranslation($translation);
        }
    }

    /**
     * @return Collection|TranslationInterface[]
     */
    public function getNewTranslations()
    {
        // initialize collection, usually in ctor
        if ($this->newTranslations === null) {
            $this->newTranslations = new ArrayCollection();
        }

        return $this->newTranslations;
    }

    public function addTranslation(TranslationInterface $translation): void
    {
        $this->getTranslations()->set((string) $translation->getLocale(), $translation);
        $translation->setTranslatable($this);
    }

    public function removeTranslation(TranslationInterface $translation): void
    {
        $this->getTranslations()->removeElement($translation);
    }

    /**
     * Returns translation for specific locale (creates new one if doesn't exists).
     * If requested translation doesn't exist, it will first try to fallback default locale
     * If any translation doesn't exist, it will be added to newTranslations collection.
     * In order to persist new translations, call mergeNewTranslations method, before flush
     *
     * @param string $locale The locale (en, ru, fr) | null If null, will try with current locale
     */
    public function translate(?string $locale = null, bool $fallbackToDefault = true): TranslationInterface
    {
        return $this->doTranslate($locale, $fallbackToDefault);
    }

    /**
     * Merges newly created translations into persisted translations.
     */
    public function mergeNewTranslations(): void
    {
        foreach ($this->getNewTranslations() as $newTranslation) {
            if (! $this->getTranslations()->contains($newTranslation) && ! $newTranslation->isEmpty()) {
                $this->addTranslation($newTranslation);
                $this->getNewTranslations()->removeElement($newTranslation);
            }
        }

        foreach ($this->getTranslations() as $translation) {
            if (! $translation->isEmpty()) {
                continue;
            }

            $this->removeTranslation($translation);
        }
    }

    public function setCurrentLocale(string $locale): void
    {
        $this->currentLocale = $locale;
    }

    public function getCurrentLocale(): string
    {
        return $this->currentLocale ?: $this->getDefaultLocale();
    }

    /**
     * @param mixed $locale the default locale
     */
    public function setDefaultLocale($locale): void
    {
        $this->defaultLocale = $locale;
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    public static function getTranslationEntityClass(): string
    {
        return static::class . 'Translation';
    }

    /**
     * Returns translation for specific locale (creates new one if doesn't exists).
     * If requested translation doesn't exist, it will first try to fallback default locale
     * If any translation doesn't exist, it will be added to newTranslations collection.
     * In order to persist new translations, call mergeNewTranslations method, before flush
     *
     * @param string $locale The locale (en, ru, fr) | null If null, will try with current locale
     */
    protected function doTranslate(?string $locale = null, bool $fallbackToDefault = true): TranslationInterface
    {
        if ($locale === null) {
            $locale = $this->getCurrentLocale();
        }

        $translation = $this->findTranslationByLocale($locale);
        if ($translation and ! $translation->isEmpty()) {
            return $translation;
        }

        if ($fallbackToDefault) {
            $fallbackLocale = $this->computeFallbackLocale($locale);

            if ($fallbackLocale) {
                $translation = $this->findTranslationByLocale($fallbackLocale);
                if ($translation) {
                    return $translation;
                }
            }

            $defaultTranslation = $this->findTranslationByLocale($this->getDefaultLocale(), false);
            if ($defaultTranslation) {
                return $defaultTranslation;
            }
        }

        if ($translation) {
            return $translation;
        }

        $class = static::getTranslationEntityClass();

        /** @var TranslationInterface $translation */
        $translation = new $class();
        $translation->setLocale($locale);

        $this->getNewTranslations()->set((string) $translation->getLocale(), $translation);
        $translation->setTranslatable($this);

        return $translation;
    }

    /**
     * An extra feature allows you to proxy translated fields of a translatable entity.
     *
     * @return mixed The translated value of the field for current locale
     */
    protected function proxyCurrentLocaleTranslation(string $method, array $arguments = [])
    {
        // allow $entity->name call $entity->getName() in templates
        if (! method_exists(self::getTranslationEntityClass(), $method)) {
            $method = 'get' . ucfirst($method);
        }

        $translation = $this->translate($this->getCurrentLocale());

        return call_user_func_array([$translation, $method], $arguments);
    }

    /**
     * Finds specific translation in collection by its locale.
     */
    protected function findTranslationByLocale(string $locale, bool $withNewTranslations = true): ?TranslationInterface
    {
        $translation = $this->getTranslations()->get($locale);

        if ($translation) {
            return $translation;
        }

        if ($withNewTranslations) {
            return $this->getNewTranslations()->get($locale);
        }

        return null;
    }

    /**
     * @return false|string
     */
    protected function computeFallbackLocale($locale)
    {
        if (strrchr($locale, '_') !== false) {
            return substr($locale, 0, -strlen(strrchr($locale, '_')));
        }

        return false;
    }

    private function ensureIsIterableOrCollection($translations): void
    {
        if ($translations instanceof Collection) {
            return;
        }

        if (is_iterable($translations)) {
            return;
        }

        throw new InvalidArgumentException(sprintf(
            '$translations parameter must be iterable or %s', Collection::class)
        );
    }
}
