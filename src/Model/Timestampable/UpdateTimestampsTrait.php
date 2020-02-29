<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use DateTime;
use DateTimeZone;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;

trait UpdateTimestampsTrait
{
    /**
     * Updates createdAt and updatedAt timestamps.
     */
    public function updateTimestamps(): void
    {
        // Create a datetime with microseconds
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));

        if ($dateTime === false) {
            throw new ShouldNotHappenException();
        }

        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

        if ($this->createdAt === null) {
            $this->createdAt = $dateTime;
        }

        $this->updatedAt = $dateTime;
    }
}
