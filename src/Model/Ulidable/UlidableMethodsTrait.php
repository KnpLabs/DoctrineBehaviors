<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Ulidable;

use Symfony\Component\Uid\Ulid;

trait UlidableMethodsTrait
{
    public function setUlid($ulid): void
    {
        $this->ulid = $ulid;
    }

    public function getUlid()
    {
        if (is_string($this->ulid)) {
            return Ulid::fromString($this->ulid);
        }

        return $this->ulid;
    }

    public function generateUlid(): void
    {
        if ($this->ulid) {
            return;
        }

        $this->ulid = new Ulid();
    }
}
