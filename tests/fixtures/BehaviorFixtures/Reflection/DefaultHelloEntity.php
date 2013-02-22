<?php

namespace BehaviorFixtures\Reflection;

class DefaultHelloEntity
{
    use DefaultHelloTrait
    {
        DefaultHelloTrait::sayHello as sayTraitHello;
    }

    function sayHello()
    {
        return "It's my, MARIO !!!!";
    }

    function callHelloFunc()
    {
        return $this->callTraitMethod('BehaviorFixtures\Reflection\DefaultHelloTrait::sayHello', 'Mario', 'BROS');
    }
}