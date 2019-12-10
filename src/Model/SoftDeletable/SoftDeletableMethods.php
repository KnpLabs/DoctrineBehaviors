<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\SoftDeletable;

use DateTime;
use DateTimeInterface;
use DateTimeZone;

trait SoftDeletableMethods
{
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

    public function isDeleted(): bool
    {
        if ($this->deletedAt !== null) {
            return $this->deletedAt <= $this->currentDateTime();
        }

        return false;
    }

    public function willBeDeleted(?DateTime $at = null): bool
    {
        if ($this->deletedAt === null) {
            return false;
        }
        if ($at === null) {
            return true;
        }

        return $this->deletedAt <= $at;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTime $date)
    {
        $this->deletedAt = $date;

        return $this;
    }

    private function currentDateTime(): ?DateTimeInterface
    {
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

        return $dateTime;
    }
}
