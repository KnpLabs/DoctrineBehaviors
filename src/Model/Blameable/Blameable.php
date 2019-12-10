<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Model\Blameable;

trait Blameable
{
    use BlameableProperties;
    use BlameableMethods;
}
