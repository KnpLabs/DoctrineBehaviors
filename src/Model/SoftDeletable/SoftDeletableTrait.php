<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\SoftDeletable;

trait SoftDeletableTrait
{
    use SoftDeletablePropertiesTrait;
    use SoftDeletableMethodsTrait;
}
