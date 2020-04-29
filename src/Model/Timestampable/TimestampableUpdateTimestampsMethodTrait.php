<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

trait TimestampableUpdateTimestampsMethodTrait
{
    use TimestampableUpdateUpdatedAtFieldsMethodTrait;
    use TimestampableUpdateCreaetedAtFieldsMethodTrait;

    /**
     * Updates timestamps for createdAt and updatedAt properties.
     */
    public function updateTimestamps(): void
    {
        $dateTime = $this->getCurrentDateTime();
        $this->updatedCreatedAtFields($dateTime);
        $this->updatedUpdatedAtFields($dateTime);
    }
}
