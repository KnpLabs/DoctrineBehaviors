<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

require_once 'DefaultTranslatableTest.php';

class RenamedTranslatableTest extends DefaultTranslatableTest
{
    protected function getTestedTranslatableEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedTranslatableEntity";
    }

    protected function getTestedTranslationEntityClass()
    {
        return "\BehaviorFixtures\ORM\RenamedTranslatableEntityTranslation";
    }
}
