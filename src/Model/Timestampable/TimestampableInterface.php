<?php

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use DateTime;

/**
 * Interface TimestampableInterface
 *
 * Should be implemented by entity, that needs to be timestamped.
 */
interface TimestampableInterface
{
    /**
     * Updates createdAt and updatedAt timestamps.
     */
    public function updateTimestamps();

    /**
     * Returns createdAt value.
     *
     * @return DateTime
     */
    public function getCreatedAt();

    /**
     * @param DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt);

    /**
     * Returns updatedAt value.
     *
     * @return DateTime
     */
    public function getUpdatedAt();

    /**
     * @param DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt(DateTime $updatedAt);
}
