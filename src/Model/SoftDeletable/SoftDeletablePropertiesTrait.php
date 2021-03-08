<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\SoftDeletable;

use DateTimeInterface;

trait SoftDeletablePropertiesTrait
{
    protected ?DateTimeInterface $deletedAt = null;
}
