<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Provider;

use Knp\DoctrineBehaviors\Contract\Provider\LocationProviderInterface;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

final class TestLocationProvider implements LocationProviderInterface
{
    /**
     * @var Point|null
     */
    private $point;

    public function __construct()
    {
        $this->point = Point::fromArray([
            'longitude' => 47.7,
            'latitude' => 7.9,
        ]);
    }

    public function providePoint(): ?Point
    {
        return $this->point;
    }
}
