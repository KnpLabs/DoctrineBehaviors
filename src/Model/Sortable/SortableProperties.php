<?php

namespace Knp\DoctrineBehaviors\Model\Sortable;

/**
 * Trait SortableProperties
 */
trait SortableProperties
{
    /**
     * @var int
     */
    protected $sort = 1;

    /**
     * @var bool
     */
    private $reordered = false;
}
