<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

/**
 * Timestampable trait.
 *
 * Should be used inside entity, that needs to be timestamped.
 */
trait Timestampable
{
    use TimestampableProperties;
    use TimestampableMethods
    ;
}
