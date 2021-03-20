<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Sluggable;

trait SluggableMethodsTrait
{
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function shouldGenerateUniqueSlugs(): bool
    {
        return false;
    }

    public function getSlugDelimiter(): string
    {
        return '-';
    }

    public function shouldRegenerateSlugOnUpdate(): bool
    {
        return true;
    }
}
