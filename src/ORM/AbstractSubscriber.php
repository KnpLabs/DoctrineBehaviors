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
    protected $isRecursive = false;

    /**
     * @var ClassAnalyzer
     */
    private $classAnalyzer;

    public function __construct(ClassAnalyzer $classAnalyzer, $isRecursive)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->isRecursive = (bool) $isRecursive;
    }

    abstract public function getSubscribedEvents();

    protected function getClassAnalyzer(): ClassAnalyzer
    {
        return $this->classAnalyzer;
    }
}
