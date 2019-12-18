<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

use Ramsey\Uuid\UuidInterface;

interface UuidableInterface
{
    public function setUuid(UuidInterface $uuid): void;

    public function getUuid(): ?UuidInterface;

    public function generateUuid(): void;
}
