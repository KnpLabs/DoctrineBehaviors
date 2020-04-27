<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;

trait TimestampableCurrentDateTimeMethodTrait
{
    /**
     * Returns a DateTimeInterface object with the current timestamp.
     *
     * @throws ShouldNotHappenException
     */
    public function getCurrentDateTime(): DateTimeInterface
    {
        // Create a datetime with microseconds.
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));

        if ($dateTime === false) {
            throw new ShouldNotHappenException();
        }

        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

        return $dateTime;
    }
}
