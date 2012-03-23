<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Blameable;

/**
 * Blameable trait.
 *
 * Should be used inside entity where you need to track which user created or updated it
 */
trait Blameable
{
    /**
     * Will be mapped to either string or UserInterface entity
     * by BlameableListener
     */
    private $createdBy;

    /**
     * Will be mapped to either string or UserInterface entity
     * by BlameableListener
     */
    private $updatedBy;

    /**
     * @param string|UserInterface the user representation
     */
    public function setCreatedBy($user)
    {
        $this->createdBy = $user;
    }

    /**
     * @param string|UserInterface the user representation
     */
    public function setUpdatedBy($user)
    {
        $this->updatedBy = $user;
    }

    /**
     * @return string|UserInterface the user who created entity
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return string|UserInterface the user who last updated entity
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
