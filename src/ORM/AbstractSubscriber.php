<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

abstract class AbstractSubscriber implements EventSubscriber
{
    /**
     * @var bool
     */
    protected $isRecursive;

    /**
     * @var ClassAnalyzer
     */
    private $classAnalyser;

    public function __construct(ClassAnalyzer $classAnalyser, $isRecursive)
    {
        $this->classAnalyser = $classAnalyser;
        $this->isRecursive = (bool) $isRecursive;
    }

    abstract public function getSubscribedEvents();

    protected function getClassAnalyzer(): ClassAnalyzer
    {
        return $this->classAnalyser;
    }
}
