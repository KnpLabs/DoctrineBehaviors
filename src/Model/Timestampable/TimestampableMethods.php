<?php

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use Knp\DoctrineBehaviors\ORM\Trackable\TrackedEventArgs;

/**
 * Timestampable trait.
 *
 * Should be used inside entity, that needs to be timestamped.
 */
trait TimestampableMethods
{
    /**
     * Returns createdAt value.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Returns updatedAt value.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Returns deletedAt value.
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @param \DateTime deletedAt
     * @return $this
     */
    public function setDeletedAt(\DateTime $deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function trackTimestampableCreation(TrackedEventArgs $eventArgs)
    {
        $this->createdAt = $eventArgs->getMetadata()->get('timestamp');
    }

    public function trackTimestampableChange(TrackedEventArgs $eventArgs)
    {
        $this->updatedAt = $eventArgs->getMetadata()->get('timestamp');
    }

    public function trackTimestampableDeletion(TrackedEventArgs $eventArgs)
    {
        $this->deletedAt = $eventArgs->getMetadata()->get('timestamp');
    }
}
