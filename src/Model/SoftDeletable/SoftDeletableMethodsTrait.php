<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\SoftDeletable;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;

trait SoftDeletableMethodsTrait
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

    public function willBeDeleted(?DateTimeInterface $dateTime = null): bool
    {
        if ($this->deletedAt === null) {
            return false;
        }
        if ($dateTime === null) {
            return true;
        }

        return $this->deletedAt <= $dateTime;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeInterface $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    private function currentDateTime(): DateTimeInterface
    {
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        if ($dateTime === false) {
            throw new ShouldNotHappenException();
        }

        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

        return $dateTime;
    }
}
