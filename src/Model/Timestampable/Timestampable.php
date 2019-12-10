<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Timestampable;

trait Timestampable
{
    use TimestampableProperties;
    use TimestampableMethods;
}
