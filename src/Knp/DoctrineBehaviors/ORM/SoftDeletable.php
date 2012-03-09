<?php

namespace Knp\DoctrineBehaviors\ORM;

use Doctrine\ORM\Mapping as ORM;

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
}
