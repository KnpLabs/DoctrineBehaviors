<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use DateTimeInterface;

trait TimestampableMethodsTrait
{
    use UpdateTimestampsTrait;

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
