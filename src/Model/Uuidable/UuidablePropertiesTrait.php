<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Uuidable;

use Ramsey\Uuid\UuidInterface;

trait UuidablePropertiesTrait
{
    /**
     * @var UuidInterface|string|null
     */
    private $uuid;
}
