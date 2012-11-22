<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\SoftDeletable;

/**
 * SoftDeletable trait.
 *
 * Should be used inside entity, that needs to be self-deleted.
 */
trait SoftDeletable
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     * Marks entity as deleted.
     */
    public function delete()
    {
        $this->deletedAt = new \DateTime();
    }

    /**
     * Restore entity by undeleting it
     */
    public function restore()
    {
        $this->deletedAt = null;
    }

    /**
     * Checks whether the entity has been deleted.
     *
     * @return Boolean
     */
    public function isDeleted()
    {
        return null !== $this->deletedAt;
    }

    /**
     * Returns date on which entity was been deleted.
     *
     * @return DateTime|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
}
