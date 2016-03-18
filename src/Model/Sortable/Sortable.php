<?php

namespace Knp\DoctrineBehaviors\Model\Sortable;

trait Sortable
{
    protected $sort = 1;

    private $reordered = false;

    /**
     * Get sort.
     *
     * @return sort.
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set sort.
     *
     * @param sort the value to set.
     *
     * @return $this
     */
    public function setSort($sort)
    {
        $this->reordered = $this->sort !== $sort;
        $this->sort      = $sort;

        return $this;
    }

    public function isReordered()
    {
        return $this->reordered;
    }

    /**
     * @param bool $reordered.
     * 
     * @return $this
     */
    public function setReordered($reordered = true)
    {
        $this->reordered = (bool)$reordered;

        return $this;
    }

    /**
     * Reorder items
     * 
     * @return $this
     */
    public function reorder()
    {
        $this->reordered = true;
        
        return $this;
    }
}
