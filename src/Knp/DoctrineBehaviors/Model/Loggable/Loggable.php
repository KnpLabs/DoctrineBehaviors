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
 * Loggable trait.
 *
 * Should be used inside entity where you need to track modifications log
 */
trait Loggable
{
    /**
     * @return string some log informations
     */
    public function getUpdateLogMessage(array $changeSets = [])
    {
        $message = [];
        foreach ($changeSets as $property => $changeSet) {
            $message[] = sprintf(
                '%s #%d : property "%s" changed from "%s" to "%s"',
                __CLASS__,
                $this->getId(),
                $property,
                $changeSet[0],
                $changeSet[1]
            );
        }

        return implode("\n", $message);
    }

    public function getRemoveLogMessage()
    {
        return sprintf('%s #%d removed', __CLASS__, $this->getId());
    }
}
