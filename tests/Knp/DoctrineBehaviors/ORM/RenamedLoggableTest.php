<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultLoggableTest.php';

class RenamedLoggableTest extends DefaultLoggableTest
{
    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedLoggableEntity";
    }
}
