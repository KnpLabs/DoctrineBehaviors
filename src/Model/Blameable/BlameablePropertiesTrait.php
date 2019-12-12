<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Blameable;

trait BlameablePropertiesTrait
{
    /**
     * @var string|int|object
     */
    protected $createdBy;

    /**
     * @var string|int|object
     */
    protected $updatedBy;

    /**
     * @var string|int|object
     */
    protected $deletedBy;
}
