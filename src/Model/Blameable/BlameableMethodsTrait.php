<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Blameable;

trait BlameableMethodsTrait
{
    /**
     * @param string|int|object $user
     */
    public function setCreatedBy($user): void
    {
        $this->createdBy = $user;
    }

    /**
     * @param string|int|object $user
     */
    public function setUpdatedBy($user): void
    {
        $this->updatedBy = $user;
    }

    /**
     * @param string|int|object $user
     */
    public function setDeletedBy($user): void
    {
        $this->deletedBy = $user;
    }

    /**
     * @return int|object|string
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @return int|object|string
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @return string|int|object|null
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }
}
