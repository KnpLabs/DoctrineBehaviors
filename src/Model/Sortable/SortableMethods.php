<?php

namespace Knp\DoctrineBehaviors\Model\Sortable;

/**
 * Trait SortableMethods
 */
trait SortableMethods
{
    /**
     * Get sort.
     *
     * @return int The sort value
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set sort.
     *
     * @param int $sort The value to set.
     *
     * @return $this
     */
    public function setSort($sort)
    {
        $this->reordered = $this->sort !== $sort;
        $this->sort      = $sort;

        return $this;
    }

    /**
     * Whether reordered is true or false.
     *
     * @return bool
     */
    public function isReordered()
    {
        return $this->reordered;
    }

    /**
     * Set reordered to true.
     *
     * @return $this
     */
    public function setReordered()
    {
        $this->reordered = true;

        return $this;
    }
}
