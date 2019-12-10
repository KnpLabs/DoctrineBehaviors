<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

interface TranslationInterface
{
    public static function getTranslatableEntityClass(): string;

    public function setTranslatable(TranslatableInterface $translatable): void;

    public function getTranslatable(): TranslatableInterface;

    public function setLocale(string $locale): void;

    public function getLocale(): string;

    public function isEmpty(): bool;
}
