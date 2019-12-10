<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use DateTime;
use DateTimeZone;

trait TimestampableMethods
{
    /**
     * Returns createdAt value.
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Returns updatedAt value.
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Updates createdAt and updatedAt timestamps.
     */
    public function updateTimestamps(): void
    {
        // Create a datetime with microseconds
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));
        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

        if ($this->createdAt === null) {
            $this->createdAt = $dateTime;
        }

        $this->updatedAt = $dateTime;
    }
}
