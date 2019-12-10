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
    protected $classAnalyzer;

    public function __construct(bool $isRecursive)
    {
        $this->isRecursive = (bool) $isRecursive;
    }

    /**
     * @required
     */
    public function autowireAbstractSubscriber(ClassAnalyzer $classAnalyzer): void
    {
        $this->classAnalyzer = $classAnalyzer;
    }

    /**
     * @return string[]
     */
    abstract public function getSubscribedEvents();
}
