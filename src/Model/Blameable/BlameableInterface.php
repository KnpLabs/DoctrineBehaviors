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
 * Blameable interface.
 *
 * Should be used to tag entities where you need to track which user created, updated or deleted it
 */
interface BlameableInterface
{
    /**
     * @param mixed the user representation
     * @return $this
     */
    public function setCreatedBy($user);

    /**
     * @param mixed the user representation
     * @return $this
     */
    public function setUpdatedBy($user);

    /**
     * @param mixed the user representation
     * @return $this
     */
    public function setDeletedBy($user);

    /**
     * @return mixed the user who created entity
     */
    public function getCreatedBy();

    /**
     * @return mixed the user who last updated entity
     */
    public function getUpdatedBy();

    /**
     * @return mixed the user who removed entity
     */
    public function getDeletedBy();

    public function isBlameable();
}
