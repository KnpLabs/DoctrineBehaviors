<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use DateTimeInterface;

trait TimestampablePropertiesTrait
{
    protected DateTimeInterface $createdAt;

    protected DateTimeInterface $updatedAt;
}
