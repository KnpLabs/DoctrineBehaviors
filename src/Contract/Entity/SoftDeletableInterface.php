<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

use DateTimeInterface;

interface SoftDeletableInterface
{
    public function delete(): void;

    public function restore(): void;

    public function isDeleted(): bool;

    /**
     * Checks whether the entity will be deleted.
     */
    public function willBeDeleted(?DateTimeInterface $deletedAt = null): bool;

    public function getDeletedAt(): ?DateTimeInterface;

    public function setDeletedAt(DateTimeInterface $deletedAt): void;
}
