<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

use Doctrine\Common\Collections\Collection;

/**
 * @template T of \Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface
 */
trait TranslatablePropertiesTrait
{
    /**
     * @var Collection<string, T>
     */
    protected $translations;

    /**
     * @see mergeNewTranslations
     * @var Collection<string, T>
     */
    protected $newTranslations;

    /**
     * currentLocale is a non persisted field configured during postLoad event
     *
     * @var string|null
     */
    protected $currentLocale;

    /**
     * @var string
     */
    protected $defaultLocale = 'en';
}
