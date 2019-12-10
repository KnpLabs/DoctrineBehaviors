<?php

declare(strict_types=1);

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\ORM\Geocodable\GeocodableRepository;

class GeocodableEntityRepository extends EntityRepository
{
    use GeocodableRepository;
}
