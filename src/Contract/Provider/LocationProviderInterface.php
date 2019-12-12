<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Provider;

use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

interface LocationProviderInterface
{
    public function providePoint(): ?Point;
}
