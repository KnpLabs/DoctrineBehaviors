<?php

namespace Knp\DoctrineBehaviors\Model\Sortable;

/**
 * SortableInterface interface.
 *
 * Should be implemented by an entity that needs to be sorted.
 */
interface SortableInterface
{
    /**
     * Get sort.
     *
     * @return int
     */
    public function getSort();

    /**
     * Set sort.
     *
     * @param int $sort
     */
    public function setSort($sort);

    /**
     * Whether is reordered.
     *
     * @return bool
     */
    public function isReordered();

    /**
     * Set reordered.
     */
    public function setReordered();
}
