<?php

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

abstract class AbstractListener implements EventSubscriber
{

    private $classAnalyser;

    public function __construct(ClassAnalyzer $classAnalyser)
    {
        $this->classAnalyser = $classAnalyser;
    }

    protected function getClassAnalyzer()
    {
        return $this->classAnalyser;
    }

    abstract public function getSubscribedEvents();

}