<?php

declare(strict_types=1);

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\ORM\Geocodable;

class GeocodableEntityRepository extends EntityRepository
{
    use Geocodable\GeocodableRepository;
}
