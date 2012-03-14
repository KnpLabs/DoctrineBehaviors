<?php

namespace Knp\DoctrineBehaviors\ORM\Sortable;

/**
 * Class documentation
 *
 * @author     Florian Klein <florian.klein@free.fr>
 */
trait SortableEntity
{
    /**
     * @ORM\Column(type="integer")
     */
    private $sort = 1;

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
        $this->sort = $sort;
    }
}

