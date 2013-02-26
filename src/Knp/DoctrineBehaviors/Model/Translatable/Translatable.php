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
use Knp\DoctrineBehaviors\Reflection\Renamable;

/**
 * Translatable trait.
 *
 * Should be used inside entity, that needs to be translated.
 */
trait Translatable
{
    use Renamable;

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
        $this
            ->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getTranslations')
            ->set(
                $translation->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translation::getLocale'),
                $translation
            )

        ;

        $translation->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translation::setTranslatable', $this);
    }

    /**
     * Removes specific translation.
     *
     * @param Translation $translation The translation
     */
    public function removeTranslation($translation)
    {
        $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getTranslations')->removeElement($translation);
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
        return $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::doTranslate', $locale);
    }

    protected function doTranslate($locale = null)
    {

        if (null === $locale) {
            $locale = $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getCurrentLocale');
        }

        $translation = $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::findTranslationByLocale', $locale);

        if ($translation) {
            if (!$translation->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translation::isEmpty')) {
                
                return $translation;
            }
        }

        if ($defaultTranslation = $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::findTranslationByLocale', $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getDefaultLocale'), false)) {
            
            return $defaultTranslation;
        }

        $getTranslationEntityClass = (new ClassAnalyzer)->getTraitMethodName(
            new \ReflectionClass($this),
            'Knp\DoctrineBehaviors\Model\Translatable\Translatable',
            'getTranslationEntityClass'
        );

        $class       = self::{$getTranslationEntityClass}();
        $translation = new $class();

        $translation->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translation::setLocale', $locale);

        $this
            ->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getNewTranslations')
            ->set(
                $translation->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translation::getLocale'),
                $translation
            )
        ;

        $translation->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translation::setTranslatable', $this);

        return $translation;
    }

    /**
     * Merges newly created translations into persisted translations.
     */
    public function mergeNewTranslations()
    {
        foreach ($this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getNewTranslations') as $newTranslation) {
            if (!$this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getTranslations')->contains($newTranslation)) {
                $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::addTranslation', $newTranslation);
                $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getNewTranslations')->removeElement($newTranslation);
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
        return $this->currentLocale ?: $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getDefaultLocale');
    }

    public function getDefaultLocale()
    {
        return 'en';
    }

    protected function proxyCurrentLocaleTranslation($method, array $arguments = [])
    {
        return call_user_func_array(
            [
                $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::translate',$this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getCurrentLocale')),
                $method
            ],
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
        $translation = $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getTranslations')->get($locale);

        if ($translation) {
            return $translation;
        }

        if ($withNewTranslations) {
            return $this->callTraitMethod('Knp\DoctrineBehaviors\Model\Translatable\Translatable::getNewTranslations')->get($locale);
        }
    }
}
