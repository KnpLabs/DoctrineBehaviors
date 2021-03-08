<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

use Doctrine\Common\Collections\Collection;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;

trait TranslatablePropertiesTrait
{
    /**
     * @var Collection<TranslationInterface>
     */
    protected Collection $translations;

    /**
     * @see mergeNewTranslations
     * @var Collection<TranslationInterface>
     */
    protected Collection $newTranslations;

    /**
     * currentLocale is a non persisted field configured during postLoad event
     */
    protected ?string $currentLocale;

    protected string $defaultLocale = 'en';
}
