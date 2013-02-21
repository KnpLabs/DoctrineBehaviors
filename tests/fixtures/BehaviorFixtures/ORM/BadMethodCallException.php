<?php

namespace BehaviorFixtures\ORM;

class BadMethodCallException extends \Exception {

    function __construct($entity, $method) {

        $class = get_class($entity);

        parent::__construct(
            sprintf('The method "%s::%s" is renamed but still be called.', $class, $method),
            1
        );

    }

}