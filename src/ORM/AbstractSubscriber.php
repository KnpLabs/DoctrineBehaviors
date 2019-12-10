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

    public function __construct(ClassAnalyzer $classAnalyzer, bool $isRecursive)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->isRecursive = (bool) $isRecursive;
    }

    /**
     * @return string[]
     */
    abstract public function getSubscribedEvents();

    protected function getClassAnalyzer(): ClassAnalyzer
    {
        return $this->classAnalyzer;
    }
}
