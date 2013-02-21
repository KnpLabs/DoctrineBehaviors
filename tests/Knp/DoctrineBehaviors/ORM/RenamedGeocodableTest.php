<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultGeocodableTest.php';

class RenamedGeocodableTest extends DefaultGeocodableTest
{
    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedGeocodableEntity";
    }
}
