<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

trait TimestampablePropertiesMetadataTrait
{
    /**
     * Returns an array of createdAt properties.
     *
     * @return string[]
     */
    public function getCreatedAtProperties(): array
    {
        return ['createdAt'];
    }

    /**
     * Returns an array of updatedAt properties.
     *
     * @return string[]
     */
    public function getUpdatedAtProperties(): array
    {
        return ['updatedAt'];
    }
}
