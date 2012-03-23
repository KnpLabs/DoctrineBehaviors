<?php

namespace Knp\DoctrineBehaviors\ORM\Blameable;

trait Blameable
{
    private $createdBy;

    private $updatedBy;

    public function setCreatedBy($user)
    {
        $this->createdBy = $user;
    }

    public function setUpdatedBy($user)
    {
        $this->updatedBy = $user;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
