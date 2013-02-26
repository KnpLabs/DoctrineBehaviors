<?php

namespace Tests\Knp\DoctrineBehaviors\Reflection;

use BehaviorFixtures\Reflection\DefaultHelloEntity;

class RenamableTraitTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function it_should_call_the_renamed_method()
    {
        $entity = new DefaultHelloEntity;

        $this->assertEquals("Hello Mario BROS.", $entity->callHelloFunc());

        $this->assertEquals("It's my, MARIO !!!!", $entity->sayHello());
    }
}
