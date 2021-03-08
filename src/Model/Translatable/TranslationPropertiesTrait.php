<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;

trait TranslationPropertiesTrait
{
    protected string $locale;

    /**
     * Will be mapped to translatable entity by TranslatableSubscriber
     */
    protected TranslatableInterface $translatable;
}
