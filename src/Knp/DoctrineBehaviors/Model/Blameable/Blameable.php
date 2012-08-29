<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\Blameable;

/**
 * Blameable trait.
 *
 * Should be used inside entity where you need to track which user created or updated it
 */
trait Blameable
{
    use BlameableMethods;

    /**
     * Will be mapped to either string or user entity
     * by BlameableListener
     */
    private $createdBy;

    /**
     * Will be mapped to either string or user entity
     * by BlameableListener
     */
    private $updatedBy;
}
