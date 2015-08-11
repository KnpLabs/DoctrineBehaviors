<?php

namespace Knp\DoctrineBehaviors\Model\SoftDeletable;

/**
 * SoftDeletable interface.
 *
 * Should be implemented by entity, that needs to be self-deleted.
 */
interface SoftDeletableInterface
{
    /**
     * Marks entity as deleted.
     */
    public function delete();

    /**
     * Restore entity by undeleting it
     */
    public function restore();

    /**
     * Checks whether the entity has been deleted.
     *
     * @return bool
     */
    public function isDeleted();

    /**
     * Checks whether the entity will be deleted.
     *
     * @param \DateTime $at
     *
     * @return bool
     */
    public function willBeDeleted(\DateTime $at = null);

    /**
     * Returns date on which entity was been deleted.
     *
     * @return \DateTime|null
     */
    public function getDeletedAt();

    /**
     * Set the delete date to given date.
     *
     * @param \DateTime $deletedAt
     */
    public function setDeletedAt(\DateTime $deletedAt);
}
