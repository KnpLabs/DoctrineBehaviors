<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Translatable;

use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Nette\Utils\Strings;

/**
 * @template T of TranslatableInterface
 */
trait TranslationMethodsTrait
{
    /**
     * @return class-string<T>
     */
    public static function getTranslatableEntityClass(): string
    {
        // By default, the translatable class has the same name but without the "Translation" suffix
        return Strings::substring(static::class, 0, -11);
    }

    /**
     * Sets entity, that this translation should be mapped to.
     *
     * @param T $translatable
     */
    public function setTranslatable(TranslatableInterface $translatable): void
    {
        $this->translatable = $translatable;
    }

    /**
     * Returns entity, that this translation is mapped to.
     *
     * @return T
     */
    public function getTranslatable(): TranslatableInterface
    {
        return $this->translatable;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function isEmpty(): bool
    {
        foreach (get_object_vars($this) as $var => $value) {
            if (in_array($var, ['id', 'translatable', 'locale'], true)) {
                continue;
            }

            if (is_string($value) && strlen(trim($value)) > 0) {
                return false;
            }

            if (! empty($value)) {
                return false;
            }
        }

        return true;
    }
}
