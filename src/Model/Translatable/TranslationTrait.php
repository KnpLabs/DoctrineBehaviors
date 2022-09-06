<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

/**
 * @template T of \Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface
 */
trait TranslationTrait
{
    /**
     * @template-use TranslationPropertiesTrait<T>
     */
    use TranslationPropertiesTrait;

    /**
     * @template-use TranslationMethodsTrait<T>
     */
    use TranslationMethodsTrait;
}
