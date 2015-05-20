<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\State;

/**
 * Activable trait.
 *
 * Should be used inside entity where you need to track it's state
 */
trait Activatable
{
    use ActivatableProperties,
        ActivatableMethods;
}