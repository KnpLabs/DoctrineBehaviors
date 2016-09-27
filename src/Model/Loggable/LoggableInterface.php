<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\Loggable;

/**
 * Loggable interface.
 *
 * Should be used to tag entities where you need to track modification logs
 */
interface LoggableInterface
{
    /**
     * @return string some log informations
     */
    public function getUpdateLogMessage(array $changeSets = []);

    public function getCreateLogMessage();

    public function getRemoveLogMessage();
}
