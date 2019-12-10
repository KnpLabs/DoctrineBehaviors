<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Geocodable;

use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

trait GeocodableMethods
{
    public function getLocation(): Point
    {
        return $this->location;
    }

    public function setLocation(?Point $point = null): void
    {
        $this->location = $point;
    }
}
