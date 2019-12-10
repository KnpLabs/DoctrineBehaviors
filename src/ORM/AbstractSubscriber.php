<?php

declare(strict_types=1);

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM;

use Doctrine\Common\EventSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

abstract class AbstractSubscriber implements EventSubscriber
{
    protected $isRecursive;

    private $classAnalyser;

    public function __construct(ClassAnalyzer $classAnalyser, $isRecursive)
    {
        $this->classAnalyser = $classAnalyser;
        $this->isRecursive = (bool) $isRecursive;
    }

    abstract public function getSubscribedEvents();

    protected function getClassAnalyzer()
    {
        return $this->classAnalyser;
    }
}
