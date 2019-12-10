<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\ORM;

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\ORM\Geocodable\GeocodableRepository;

final class GeocodableEntityRepository extends EntityRepository
{
    use GeocodableRepository;
}
