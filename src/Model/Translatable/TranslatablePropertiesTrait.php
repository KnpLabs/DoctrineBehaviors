<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

use Doctrine\Common\Collections\Collection;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;

trait TranslatablePropertiesTrait
{
    /**
     * @var Collection<TranslationInterface>|null
     */
    protected ?Collection $translations = null;

    /**
     * @see mergeNewTranslations
     * @var Collection<TranslationInterface>|null
     */
    protected ?Collection $newTranslations = null;

    /**
     * currentLocale is a non persisted field configured during postLoad event
     */
    protected ?string $currentLocale = null;

    protected string $defaultLocale = 'en';
}
