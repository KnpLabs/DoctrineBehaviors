<?php

namespace Knp\DoctrineBehaviors\Model\Sortable;

trait Sortable
{
    /**
     * @ORM\Column(type="integer")
     */
    private $sort = 1;

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
     */
    public function setSort($sort)
    {
        $this->reordered = $this->sort !== $sort;
        $this->sort      = $sort;
    }

    public function isReordered()
    {
        return $this->reordered;
    }

    public function setReordered()
    {
        $this->reordered = true;
    }
}
