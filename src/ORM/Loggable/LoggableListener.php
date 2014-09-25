<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Loggable;

/**
 * @deprecated
 */
final class LoggableListener extends LoggableSubscriber
{
    public function __construct()
    {
        trigger_error('use LoggableSubscriber instead.', E_USER_DEPRECATED);
        call_user_func_array(array('parent', '__construct'), func_get_args());
    }
}
