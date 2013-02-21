<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultTimestampableTest.php';

class RenamedTimestampableTest extends DefaultTimestampableTest
{
    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedTimestampableEntity";
    }
}
