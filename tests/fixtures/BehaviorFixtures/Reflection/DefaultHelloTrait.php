<?php

namespace BehaviorFixtures\Reflection;

use Knp\DoctrineBehaviors\Reflection\Renamable;

trait DefaultHelloTrait
{
    use Renamable;

    private $hello = 'Hello';

    public function sayHello($firstname, $lastname)
    {
        return sprintf(
            '%s %s %s.',
            $this->hello,
            $firstname,
            $lastname
        );
    }
}