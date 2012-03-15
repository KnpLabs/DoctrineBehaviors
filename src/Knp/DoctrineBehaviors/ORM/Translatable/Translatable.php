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
        $translations = $this->getTranslations()->filter(function($translation) use ($locale) {
            return $locale === $translation->getLocale();
        });

        if (count($translations)) {
            return $translations[0];
        }

        $class       = get_class($this).'Translation';
        $translation = new $class($this, $locale);

        $this->getTranslations()->add($translation);

        return $translation;
    }
}
