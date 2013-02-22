<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Reflection;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

trait Renamable
{   
 
    private $renamedTraitMethodClassAnalyser;

    final private function getRenamedTraitMethodClassAnalyser()
    {
        if (null === $this->renamedTraitMethodClassAnalyser) {
            $this->renamedTraitMethodClassAnalyser = new ClassAnalyzer;
        }

        return $this->renamedTraitMethodClassAnalyser;
    }

    public function callTraitMethod() 
    {
        $args = func_get_args();

        if (0 === count($args)) {
            throw new InvalidArgumentException("First argument of 'callTraitMethod' function should be a string like 'TraitName::MethodName'. No argument given.");
        }

        if (!is_string($args[0])) {
            throw new InvalidArgumentException("First argument of 'callTraitMethod' function should be a string like 'TraitName::MethodName'. " . get_class($args[0]) . ' given.');
        }

        $explodedName = explode('::', $args[0]);

        $methodName = $this
            ->getRenamedTraitMethodClassAnalyser()
            ->getRealTraitMethodName(
                new \ReflectionClass($this),
                $explodedName[0],
                $explodedName[1]
            )
        ;

        if (null === $methodName) {
            throw new MethodNotFoundException(
                sprintf(
                    "Method %s not found on class %s",
                    $methodName,
                    get_class($this)
                ),
                $this,
                $methodName
            );
        }

        return call_user_func_array(
            sprintf(
                '%s::%s',
                get_class($this),
                $methodName
            ),
            array_slice($args, 1)
        );
    }

}