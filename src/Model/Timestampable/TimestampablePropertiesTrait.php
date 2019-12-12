<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use DateTimeInterface;

trait TimestampablePropertiesTrait
{
    /**
     * @var DateTimeInterface
     */
    protected $createdAt;

    /**
     * @var DateTimeInterface
     */
    protected $updatedAt;
}
