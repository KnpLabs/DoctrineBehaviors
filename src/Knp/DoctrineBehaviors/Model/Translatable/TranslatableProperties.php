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
 * Translatable trait.
 *
 * Should be used inside entity, that needs to be translated.
 */
trait TranslatableProperties
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
}
