<?php

declare(strict_types=1);

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\Translatable;

trait TranslationMethods
{
    /**
     * @return string
     */
    public static function getTranslatableEntityClass()
    {
        // By default, the translatable class has the same name but without the "Translation" suffix
        return substr(self::class, 0, -11);
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets entity, that this translation should be mapped to.
     *
     * @param Translatable $translatable The translatable
     *
     * @return $this
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;

        return $this;
    }

    /**
     * Returns entity, that this translation is mapped to.
     *
     * @return Translatable
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * Sets locale name for this translation.
     *
     * @param string $locale The locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns this translation locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Tells if translation is empty
     *
     * @return bool true if translation is not filled
     */
    public function isEmpty()
    {
        foreach (get_object_vars($this) as $var => $value) {
            if (in_array($var, ['id', 'translatable', 'locale'], true)) {
                continue;
            }

            if (! empty($value)) {
                return false;
            }
        }

        return true;
    }
}
