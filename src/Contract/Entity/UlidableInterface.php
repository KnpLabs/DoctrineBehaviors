<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

interface UlidableInterface
{
    public function setUlid($ulid): void;

    public function getUlid();

    public function generateUlid(): void;
}
