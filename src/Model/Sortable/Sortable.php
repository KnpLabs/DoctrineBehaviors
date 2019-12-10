<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Sortable;

trait Sortable
{
    /**
     * @var int
     */
    protected $sort = 1;

    /**
     * @var bool
     */
    private $reordered = false;

    /**
     * Get sort.
     *
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set sort.
     *
     * @param int $sort Sort the value to set
     */
    public function setSort($sort)
    {
        $this->reordered = $this->sort !== $sort;
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReordered()
    {
        return $this->reordered;
    }

    public function setReordered()
    {
        $this->reordered = true;

        return $this;
    }
}
