<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Entity;

use DateTimeInterface;

interface TimestampableInterface
{
    /**
     * Updates timestamps for createdAt and updatedAt properties.
     */
    public function updateTimestamps(): void;

    /**
     * Returns a DateTimeInterface object with the current timestamp.
     *
     * @return DateTimeInterface|null
     */
    public static function getCurrentDateTime(): ?DateTimeInterface;

    /**
     * Returns an array of createdAt properties.
     *
     * @return array
     */
    public static function getCreatedAtProperties(): array;

    /**
     * Returns an array of updatedAt properties.
     *
     * @return array
     */
    public static function getUpdatedAtProperties(): array;
}
