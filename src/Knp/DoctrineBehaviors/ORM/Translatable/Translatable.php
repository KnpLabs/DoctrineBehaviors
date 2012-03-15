<?php

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Doctrine\Common\Collections\ArrayCollection;

trait Translatable
{
    /**
     * Will be mapped to translatable entity
     * by TranslatableListener
     */
    protected $translations;

    public function getTranslations()
    {
        return $this->translations = $this->translations ?: new ArrayCollection();
    }

    public function translate($locale)
    {
        if ($translation = $this->findTranslationByLocale($locale)) {
            return $translation;
        }

        $class       = get_class($this).'Translation';
        $translation = new $class();
        $translation->setLocale($locale);

        $this->addTranslation($translation);

        return $translation;
    }

    public function findTranslationByLocale($locale)
    {
        $translations = $this->getTranslations()->filter(function($translation) use ($locale) {
            return $locale === $translation->getLocale();
        });

        if (count($translations)) {
            return $translations[0];
        }
    }

    public function addTranslation($translation)
    {
        $translation->setTranslatable($this);
        $this->getTranslations()->add($translation);
    }

    public function removeTranslation($translation)
    {
        $this->getTranslations()->removeElement($translation);
    }
}
