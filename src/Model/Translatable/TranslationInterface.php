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

/**
 * Translation interface.
 *
 * Should be used to tag translation entities.
 */
interface TranslationInterface
{
    /**
     * Returns the translatable entity class name.
     *
     * @return string
     */
    public static function getTranslatableEntityClass();

    /**
     * Returns object id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Sets entity, that this translation should be mapped to.
     *
     * @param TranslatableInterface $translatable The translatable
     *
     * @return $this
     */
    public function setTranslatable($translatable);

    /**
     * Returns entity, that this translation is mapped to.
     *
     * @return TranslatableInterface
     */
    public function getTranslatable();

    /**
     * Sets locale name for this translation.
     *
     * @param string $locale The locale
     *
     * @return $this
     */
    public function setLocale($locale);

    /**
     * Returns this translation locale.
     *
     * @return string
     */
    public function getLocale();

    /**
     * Tells if translation is empty
     *
     * @return bool true if translation is not filled
     */
    public function isEmpty();
}
