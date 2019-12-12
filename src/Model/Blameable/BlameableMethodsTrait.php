<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Blameable;

trait BlameableMethodsTrait
{
    public function setCreatedBy($user): void
    {
        $this->createdBy = $user;
    }

    public function setUpdatedBy($user): void
    {
        $this->updatedBy = $user;
    }

    public function setDeletedBy($user): void
    {
        $this->deletedBy = $user;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    public function getDeletedBy()
    {
        return $this->deletedBy;
    }
}
