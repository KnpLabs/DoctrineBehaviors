<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

trait TimestampableUpdateTimestampsMethodTrait
{
    /**
     * Updates timestamps for createdAt and updatedAt properties.
     */
    public function updateTimestamps(): void
    {
        $dateTime = $this->getCurrentDateTime();

        $createdAtProperties = static::getCreatedAtProperties();
        foreach ($createdAtProperties as $createdAtProperty) {
            if ($this->{$createdAtProperty} === null) {
                $this->{$createdAtProperty} = $dateTime;
            }
        }

        $updatedAtProperties = static::getUpdatedAtProperties();
        foreach ($updatedAtProperties as $updatedAtProperty) {
            $this->{$updatedAtProperty} = $dateTime;
        }
    }
}
