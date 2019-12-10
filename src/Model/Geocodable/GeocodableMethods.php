<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Geocodable;

use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

trait GeocodableMethods
{
    /**
     * Get location.
     *
     * @return Point.
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set location.
     *
     * @param Point|null $location the value to set.
     *
     * @return $this
     */
    public function setLocation(?Point $location = null)
    {
        $this->location = $location;

        return $this;
    }
}
