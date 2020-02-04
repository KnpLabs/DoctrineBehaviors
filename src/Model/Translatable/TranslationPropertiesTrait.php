<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

trait TranslationPropertiesTrait
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * Will be mapped to translatable entity by TranslatableSubscriber
     * @var mixed
     */
    protected $translatable;
}
