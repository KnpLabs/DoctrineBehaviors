<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Provider;

use Knp\DoctrineBehaviors\Contract\Provider\LocationProviderInterface;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

final class LocationProvider implements LocationProviderInterface
{
    public function providePoint(): ?Point
    {
        return null;
    }
}
