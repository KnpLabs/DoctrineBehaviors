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
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

/**
 * Translatable trait.
 *
 * Should be used inside entity, that needs to be translated.
 */
trait Translatable
{
    /**
     * Will be mapped to translatable entity
     * by TranslatableListener
     */
    private $translations;

    /**
     * Will be merged with persisted translations on mergeNewTranslations call
     *
     * @see mergeNewTranslations
     */
    private $newTranslations;

    /**
     * currentLocale is a non persisted field configured during postLoad event
     */
    private $currentLocale;

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
        $analyser   = new ClassAnalyzer;
        $rfl        = new \ReflectionClass($this);

        $getTranslations = $analyser->getRealTraitMethodName(
            $rfl,
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'getTranslations'
        );

        $getLocale = $analyser->getRealTraitMethodName(
            new \ReflectionClass($translation),
            'Knp\DoctrineBehaviors\Model\Translatable\Translation',
            'getLocale'
        );

        $setTranslatable = $analyser->getRealTraitMethodName(
            new \ReflectionClass($translation),
            'Knp\DoctrineBehaviors\Model\Translatable\Translation',
            'setTranslatable'
        );

        $this->{$getTranslations}()->set($translation->{$getLocale}(), $translation);
        $translation->{$setTranslatable}($this);
    }

    /**
     * Removes specific translation.
     *
     * @param Translation $translation The translation
     */
    public function removeTranslation($translation)
    {
        $analyser   = new ClassAnalyzer;
        $rfl        = new \ReflectionClass($this);

        $getTranslations = $analyser->getRealTraitMethodName(
            $rfl,
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'getTranslations'
        );

        $this->{$getTranslations}()->removeElement($translation);
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
        $doTranslate = (new ClassAnalyzer)
            ->getRealTraitMethodName(
                new \ReflectionClass($this),
                'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
                'doTranslate'
            )
        ;

        return $this->{$doTranslate}($locale);
    }

    protected function doTranslate($locale = null)
    {

        $analyser   = new ClassAnalyzer;
        $rfl        = new \ReflectionClass($this);

        if (null === $locale) {

            $getCurrentLocale = $analyser->getRealTraitMethodName(
                $rfl,
                'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
                'getCurrentLocale'
            );

            $locale = $this->{$getCurrentLocale}();
        }

        $findTranslationByLocale = $analyser->getRealTraitMethodName(
            $rfl,
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'findTranslationByLocale'
        );

        $getDefaultLocale = $analyser->getRealTraitMethodName(
            $rfl,
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'getDefaultLocale'
        );

        $translation = $this->{$findTranslationByLocale}($locale);

        if ($translation) {
            
            $isEmpty = $analyser->getRealTraitMethodName(
                new \ReflectionClass($translation),
                'Knp\DoctrineBehaviors\Model\Translatable\Translation',
                'isEmpty'
            );

            if (!$translation->{$isEmpty}()) {
                return $translation;
            }
        }

        if ($defaultTranslation = $this->{$findTranslationByLocale}($this->{$getDefaultLocale}(), false)) {
            return $defaultTranslation;
        }

        $getTranslationEntityClass = $analyser->getRealTraitMethodName(
            $rfl,
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'getTranslationEntityClass'
        );

        $class          = self::{$getTranslationEntityClass}();
        $translation = new $class();

        $setLocale = $analyser->getRealTraitMethodName(
            new \ReflectionClass($translation),
            'Knp\DoctrineBehaviors\Model\Translatable\Translation',
            'setLocale'
        );

        $translation->{$setLocale}($locale);

        $getNewTranslations = $analyser->getRealTraitMethodName(
            $rfl,
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'getNewTranslations'
        );

        $getLocale = $analyser->getRealTraitMethodName(
            new \ReflectionClass($translation),
            'Knp\DoctrineBehaviors\Model\Translatable\Translation',
            'getLocale'
        );

        $this->{$getNewTranslations}()->set($translation->{$getLocale}(), $translation);
        
        $setTranslatable = $analyser->getRealTraitMethodName(
            new \ReflectionClass($translation),
            'Knp\DoctrineBehaviors\Model\Translatable\Translation',
            'setTranslatable'
        );

        $translation->{$setTranslatable}($this);

        return $translation;
    }

    /**
     * Merges newly created translations into persisted translations.
     */
    public function mergeNewTranslations()
    {
        $analyser   = new ClassAnalyzer;
        $rfl        = new \ReflectionClass($this);

        $getNewTranslations = $analyser->getRealTraitMethodName(
            $rfl,
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'getNewTranslations'
        );

        $getTranslations = $analyser->getRealTraitMethodName(
            $rfl,
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'getTranslations'
        );

        $addTranslation = $analyser->getRealTraitMethodName(
            $rfl,
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'addTranslation'
        );

        foreach ($this->{$getNewTranslations}() as $newTranslation) {
            if (!$this->{$getTranslations}()->contains($newTranslation)) {
                $this->{$addTranslation}($newTranslation);
                $this->{$getNewTranslations}()->removeElement($newTranslation);
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
        $getDefaultLocale = (new ClassAnalyzer)
            ->getRealTraitMethodName(
                new \ReflectionClass($this),
                'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
                'getDefaultLocale'
            )
        ;

        return $this->currentLocale ?: $this->{$getDefaultLocale}();
    }

    public function getDefaultLocale()
    {
        return 'en';
    }

    protected function proxyCurrentLocaleTranslation($method, array $arguments = [])
    {
        $translate = (new ClassAnalyzer)
            ->getRealTraitMethodName(
                new \ReflectionClass($this),
                'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
                'translate'
            )
        ;

        $getCurrentLocale = (new ClassAnalyzer)
            ->getRealTraitMethodName(
                new \ReflectionClass($this),
                'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
                'getCurrentLocale'
            )
        ;

        return call_user_func_array(
            [$this->{$translate}($this->{$getCurrentLocale}()), $method],
            $arguments
        );
    }

    /**
     * Returns translation entity class name.
     *
     * @return string
     */
    public static function getTranslationEntityClass()
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

        $analyser   = new ClassAnalyzer;
        $rfl        = new \ReflectionClass($this);

        $getNewTranslations = $analyser->getRealTraitMethodName(
            $rfl,
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'getNewTranslations'
        );

        $getTranslations = $analyser->getRealTraitMethodName(
            $rfl,
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'getTranslations'
        );

        $translation = $this->{$getTranslations}()->get($locale);

        if ($translation) {
            return $translation;
        }

        if ($withNewTranslations) {
            return $this->{$getNewTranslations}()->get($locale);
        }
    }
}
