<?php

namespace BehaviorFixtures\ORM;

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\ORM\Geocodable;

/**
 * @author     Florian Klein <florian.klein@free.fr>
 */
class GeocodableEntityRepository extends EntityRepository
{
    use Geocodable\GeocodableRepository;
}
