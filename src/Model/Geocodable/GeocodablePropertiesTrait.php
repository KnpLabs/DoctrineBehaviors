<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Geocodable;

use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

trait GeocodablePropertiesTrait
{
    /**
     * @var Point|null
     */
    protected $location;
}
