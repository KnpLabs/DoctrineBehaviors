<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Blameable;

trait BlameableProperties
{
    /**
     * Will be mapped to either string or user entity
     * by BlameableSubscriber
     */
    protected $createdBy;

    /**
     * Will be mapped to either string or user entity
     * by BlameableSubscriber
     */
    protected $updatedBy;

    /**
     * Will be mapped to either string or user entity
     * by BlameableSubscriber
     */
    protected $deletedBy;
}
