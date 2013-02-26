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

use Knp\DoctrineBehaviors\Reflection\Renamable;

/**
 * Blameable trait.
 *
 * Should be used inside entity where you need to track which user created or updated it
 */
trait Blameable
{
    use Renamable;

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

    /**
     * Will be mapped to either string or user entity
     * by BlameableListener
     */
    private $deletedBy;

    /**
     * @param mixed the user representation
     */
    public function setCreatedBy($user)
    {
        $this->createdBy = $user;
    }

    /**
     * @param mixed the user representation
     */
    public function setUpdatedBy($user)
    {
        $this->updatedBy = $user;
    }

    /**
     * @param mixed the user representation
     */
    public function setDeletedBy($user)
    {
        $this->deletedBy = $user;
    }

    /**
     * @return mixed the user who created entity
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return mixed the user who last updated entity
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return mixed the user who removed entity
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    public function isBlameable()
    {
        return true;
    }
}
