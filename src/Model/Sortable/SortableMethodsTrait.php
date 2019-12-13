<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Sortable;

trait SortableMethodsTrait
{
    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->isReordered = $this->sort !== $sort;
        $this->sort = $sort;
    }

    public function isReordered(): bool
    {
        return $this->isReordered;
    }

    public function setReordered(): void
    {
        $this->isReordered = true;
    }
}
