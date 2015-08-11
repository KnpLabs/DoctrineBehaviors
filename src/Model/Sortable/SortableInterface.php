<?php

namespace Knp\DoctrineBehaviors\Model\Sortable;

/**
 * Interface SortableInterface
 */
interface SortableInterface
{
    /**
     * Get sort.
     *
     * @return mixed
     */
    public function getSort();

    /**
     * Set sort.
     *
     * @param mixed $sort
     *
     * @return $this
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
     *
     * @return $this
     */
    public function setReordered();
}
