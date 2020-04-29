<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

use DateTimeInterface;

trait TimestampableUpdateCreaetedAtFieldsMethodTrait
{
    protected function updatedCreatedAtFields(DateTimeInterface $dateTime): void
    {
        $createdAtProperties = $this->getCreatedAtProperties();
        foreach ($createdAtProperties as $createdAtProperty) {
            if ($this->{$createdAtProperty} === null) {
                $this->{$createdAtProperty} = $dateTime;
            }
        }
    }
}
