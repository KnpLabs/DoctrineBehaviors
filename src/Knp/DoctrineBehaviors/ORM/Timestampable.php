<?php

namespace Knp\DoctrineBehaviors\ORM;

trait Timestampable
{
    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     *
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\PrePersist
     */
    public function updateCreatedAt()
    {
        $this->createdAt = new \DateTime("now");
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateUpdatedAt()
    {
        $this->updatedAt = new \DateTime("now");
    }
}
