<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultSoftDeletableTest.php';

class RenamedSoftDeletableTest extends DefaultSoftDeletableTest
{
    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedDeletableEntity";
    }
}
