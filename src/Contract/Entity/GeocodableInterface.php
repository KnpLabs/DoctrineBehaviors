<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

interface GeocodableInterface
{
    public function getLocation(): ?Point;

    public function setLocation(?Point $point = null): void;
}
