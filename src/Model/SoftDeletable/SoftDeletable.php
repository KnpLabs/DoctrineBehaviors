<?php

declare(strict_types=1);

/*
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\Model\SoftDeletable;

trait SoftDeletable
{
    use SoftDeletableProperties;
    use SoftDeletableMethods;
}
