<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

trait TranslatableProperties
{
    /**
     * Will be mapped to translatable entity
     * by TranslatableSubscriber
     */
    protected $translations;

    /**
     * Will be merged with persisted translations on mergeNewTranslations call
     *
     * @see mergeNewTranslations
     */
    protected $newTranslations;

    /**
     * currentLocale is a non persisted field configured during postLoad event
     */
    protected $currentLocale;

    protected $defaultLocale = 'en';
}
