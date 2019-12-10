<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

interface SortableInterface
{
    public function getSort(): int;

    public function setSort(int $sort): void;

    public function isReordered(): bool;

    public function setReordered(): void;
}
