<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use DateTimeInterface;

trait TimestampableUpdateTimestampsMethodTrait
{
    /**
     * Updates timestamps for createdAt and updatedAt properties.
     */
    public function updateTimestamps(): void
    {
        $dateTime = $this->getCurrentDateTime();
        $this->updatedCreatedAtFields($dateTime);
        $this->updatedUpdatedAtFields($dateTime);
    }

    protected function updatedCreatedAtFields(DateTimeInterface $dateTime): void
    {
        $createdAtProperties = $this->getCreatedAtProperties();
        foreach ($createdAtProperties as $createdAtProperty) {
            if ($this->{$createdAtProperty} === null) {
                $this->{$createdAtProperty} = $dateTime;
            }
        }
    }

    protected function updatedUpdatedAtFields(DateTimeInterface $dateTime): void
    {
        $updatedAtProperties = $this->getUpdatedAtProperties();
        foreach ($updatedAtProperties as $updatedAtProperty) {
            $this->{$updatedAtProperty} = $dateTime;
        }
    }
}
