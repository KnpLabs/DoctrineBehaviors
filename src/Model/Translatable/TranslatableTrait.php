<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

/**
 * @template T of \Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface
 */
trait TranslatableTrait
{
    /**
     * @template-use TranslatablePropertiesTrait<T>
     */
    use TranslatablePropertiesTrait;

    /**
     * @template-use TranslatableMethodsTrait<T>
     */
    use TranslatableMethodsTrait;
}
