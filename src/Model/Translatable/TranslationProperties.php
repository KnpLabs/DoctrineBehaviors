<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

trait TranslationProperties
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $locale;

    /**
     * Will be mapped to translatable entity
     * by TranslatableSubscriber
     */
    protected $translatable;
}
