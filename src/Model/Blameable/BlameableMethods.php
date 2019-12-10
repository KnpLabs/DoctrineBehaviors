<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Blameable;

trait BlameableMethods
{
    /**
     * @param mixed $user the user representation
     */
    public function setCreatedBy($user)
    {
        $this->createdBy = $user;

        return $this;
    }

    /**
     * @param mixed $user the user representation
     */
    public function setUpdatedBy($user)
    {
        $this->updatedBy = $user;

        return $this;
    }

    /**
     * @param mixed $user the user representation
     */
    public function setDeletedBy($user)
    {
        $this->deletedBy = $user;

        return $this;
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
