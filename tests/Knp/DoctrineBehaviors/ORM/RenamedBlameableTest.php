<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultBlameableTest.php';

class RenamedBlameableTest extends DefaultBlameableTest
{
    protected function getTestedEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedBlameableEntity";
    }

}
