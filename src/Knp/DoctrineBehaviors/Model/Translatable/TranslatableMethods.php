<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\Translatable;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Translatable trait.
 *
 * Should be used inside entity, that needs to be translated.
 */
trait TranslatableMethods
{
    /**
     * Returns collection of translations.
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations = $this->translations ?: new ArrayCollection();
    }

    /**
     * Returns collection of new translations.
     *
     * @return ArrayCollection
     */
    public function getNewTranslations()
    {
        return $this->newTranslations = $this->newTranslations ?: new ArrayCollection();
    }

    /**
     * Adds new translation.
     *
     * @param Translation $translation The translation
     */
    public function addTranslation($translation)
    {
        $this->getTranslations()->set($translation->getLocale(), $translation);
        $translation->setTranslatable($this);
    }

    /**
     * Removes specific translation.
     *
     * @param Translation $translation The translation
     */
    public function removeTranslation($translation)
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
     *
     * @return Translation
     */
    public function translate($locale = null)
    {
        return $this->doTranslate($locale);
    }

    protected function doTranslate($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getCurrentLocale();
        }

        $translation = $this->findTranslationByLocale($locale);
        if ($translation and !$translation->isEmpty()) {
            return $translation;
        }

        if ($defaultTranslation = $this->findTranslationByLocale($this->getDefaultLocale(), false)) {
            return $defaultTranslation;
        }

        $class       = self::getTranslationClass();
        $translation = new $class();
        $translation->setLocale($locale);

        $this->getNewTranslations()->set($translation->getLocale(), $translation);
        $translation->setTranslatable($this);

        return $translation;
    }

    /**
     * Merges newly created translations into persisted translations.
     */
    public function mergeNewTranslations()
    {
        foreach ($this->getNewTranslations() as $newTranslation) {
            if (!$this->getTranslations()->contains($newTranslation)) {
                $this->addTranslation($newTranslation);
                $this->getNewTranslations()->removeElement($newTranslation);
            }
        }
    }

    /**
     * @param mixed $locale the current locale
     */
    public function setCurrentLocale($locale)
    {
        $this->currentLocale = $locale;
    }

    public function getCurrentLocale()
    {
        return $this->currentLocale ?: $this->getDefaultLocale();
    }

    public function getDefaultLocale()
    {
        return 'en';
    }

    protected function proxyCurrentLocaleTranslation($method, array $arguments = [])
    {
        return call_user_func_array(
            [$this->translate($this->getCurrentLocale()), $method],
            $arguments
        );
    }

    /**
     * Returns translation entity class name.
     *
     * @return string
     */
    public static function getTranslationClass()
    {
        return __CLASS__.'Translation';
    }

    /**
     * Finds specific translation in collection by its locale.
     *
     * @param string $locale              The locale (en, ru, fr)
     * @param bool   $withNewTranslations searched in new translations too
     *
     * @return Translation|null
     */
    protected function findTranslationByLocale($locale, $withNewTranslations = true)
    {
        // with index by locale
        $translation = $this->getTranslations()->get($locale);

        if ($translation) {
            return $translation;
        }

        if ($withNewTranslations) {
            $translation = $this->getNewTranslations()->get($locale);
            if ($translation) {
                return $translation;
            }
        }

        // without index by locale
        $translations = $this->getTranslations()->filter(function($translation) use ($locale) {
            return $locale === $translation->getLocale();
        });

        if ($translations->count()) {
            return $translations->first();
        }

        if (!$withNewTranslations) {
            return;
        }

        $newTranslations = $this->getNewTranslations()->filter(function($translation) use ($locale) {
            return $locale === $translation->getLocale();
        });

        return $newTranslations->first();
    }
}
