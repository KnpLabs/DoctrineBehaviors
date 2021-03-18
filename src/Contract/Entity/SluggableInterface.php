<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

interface SluggableInterface
{
    /**
     * Fields used to generate the slug.
     *
     * @return string[]
     */
    public function getSluggableFields(): array;

    public function setSlug(string $slug): void;

    public function getSlug(): string;

    public function getSlugDelimiter(): string;

    public function shouldGenerateUniqueSlugs(): bool;

    public function shouldRegenerateSlugOnUpdate(): bool;
}
