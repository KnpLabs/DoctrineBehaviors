<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\SoftDeletable;

trait SoftDeletable
{
    use SoftDeletableProperties;
    use SoftDeletableMethods;
}
