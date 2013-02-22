<?php

namespace Knp\DoctrineBehaviors\Reflection;

class MethodNotFoundException extends Exception
{
    private $subject;
    private $method;
    private $arguments;

    public function __construct($message, $subject, $method, array $arguments = array())
    {
        parent::__construct($message);

        $this->subject   = $subject;
        $this->method    = $method;
        $this->arguments = $arguments;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getArguments()
    {
        return $this->arguments;
    }
}