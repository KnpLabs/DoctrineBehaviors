<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Repository;

use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;

interface SluggableRepositoryInterface
{
    public function isSlugUniqueFor(SluggableInterface $sluggable, string $uniqueSlug): bool;

    public function isSlugUnique(
        string $uniqueSlug,
        SluggableInterface $newOrUpdated,
        SluggableInterface $exisiting
    ): bool;
}
