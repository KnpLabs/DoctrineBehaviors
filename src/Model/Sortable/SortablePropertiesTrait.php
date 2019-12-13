<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Sortable;

trait SortablePropertiesTrait
{
    /**
     * @var int
     */
    private $sort = 1;

    /**
     * @var bool
     */
    private $isReordered = false;
}
