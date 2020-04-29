<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use DateTimeInterface;

trait TimestampableUpdateUpdatedAtFieldsMethodTrait
{
    protected function updatedUpdatedAtFields(DateTimeInterface $dateTime): void
    {
        $updatedAtProperties = $this->getUpdatedAtProperties();
        foreach ($updatedAtProperties as $updatedAtProperty) {
            $this->{$updatedAtProperty} = $dateTime;
        }
    }
}
