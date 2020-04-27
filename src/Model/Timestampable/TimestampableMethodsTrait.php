<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;

trait TimestampableMethodsTrait
{
    /**
     * Updates timestamps for createdAt and updatedAt properties.
     *
     * @throws ShouldNotHappenException
     */
    public function updateTimestamps(): void
    {
        $dateTime = static::getCurrentDateTime();

        $createdAtProperties = static::getCreatedAtProperties();
        foreach ($createdAtProperties as $createdAtProperty) {
            if ($this->{$createdAtProperty} === null) {
                $this->{$createdAtProperty} = $dateTime;
            }
        }

        $updatedAtProperties = static::getCreatedAtProperties();
        foreach ($updatedAtProperties as $updatedAtProperty) {
            $this->{$updatedAtProperty} = $dateTime;
        }
    }

    /**
     * Returns a DateTimeInterface object with the current timestamp.
     *
     * @throws ShouldNotHappenException
     *
     * @return DateTimeInterface|null
     */
    public static function getCurrentDateTime(): ?DateTimeInterface
    {
        // Create a datetime with microseconds.
        $dateTime = DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));

        if ($dateTime === false) {
            throw new ShouldNotHappenException();
        }

        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));
    }

    /**
     * Returns an array of createdAt properties.
     *
     * @return array
     */
    public static function getCreatedAtProperties(): array
    {
        return ['createdAt'];
    }

    /**
     * Returns an array of updatedAt properties.
     *
     * @return array
     */
    public static function getUpdatedAtProperties(): array
    {
        return ['updatedAt'];
    }
}
