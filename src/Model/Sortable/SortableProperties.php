<?php

namespace Knp\DoctrineBehaviors\Model\Sortable;

/**
 * SortableProperties trait
 *
 * Contains properties to holds data for an entity that implements SortableInterface interface
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
