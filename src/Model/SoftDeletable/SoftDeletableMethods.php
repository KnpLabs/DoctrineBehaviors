<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\SoftDeletable;

trait SoftDeletableMethods
{
    /**
     * Marks entity as deleted.
     */
    public function delete(): void
    {
        $this->deletedAt = $this->currentDateTime();
    }

    /**
     * Restore entity by undeleting it
     */
    public function restore(): void
    {
        $this->deletedAt = null;
    }

    /**
     * Checks whether the entity has been deleted.
     *
     * @return boolean
     */
    public function isDeleted()
    {
        if ($this->deletedAt !== null) {
            return $this->deletedAt <= $this->currentDateTime();
        }

        return false;
    }

    /**
     * Checks whether the entity will be deleted.
     *
     * @return boolean
     */
    public function willBeDeleted(?\DateTime $at = null)
    {
        if ($this->deletedAt === null) {
            return false;
        }
        if ($at === null) {
            return true;
        }

        return $this->deletedAt <= $at;
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

    /**
     * Set the delete date to given date.
     *
     * @param DateTime $date
     * @param               object
     *
     * @return $this
     */
    public function setDeletedAt(\DateTime $date)
    {
        $this->deletedAt = $date;

        return $this;
    }

    /**
     * Get a instance of \DateTime with the current data time including milliseconds.
     *
     * @return \DateTime
     */
    private function currentDateTime()
    {
        $dateTime = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        $dateTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return $dateTime;
    }
}
