<?php

namespace Knp\DoctrineBehaviors\ORM\SoftDeletable;

trait SoftDeletable
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\PreRemove
     */
    public function delete()
    {
        $this->deletedAt = new \DateTime();
    }

    public function isDeleted()
    {
        return null !== $this->deletedAt;
    }

    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
}
