<?php

namespace Knp\DoctrineBehaviors\Model\Sortable;

/**
 * Sortable trait.
 *
 * Should be used inside an entity, that implements SortableInterface interface
 * and needs to be sorted.
 */
trait Sortable
{
    use SortableProperties,
        SortableMethods
    ;
}
