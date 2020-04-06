<?php
declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Common\EventSubscriber as BaseEventSubscriber;

/**
 * Interface EventSubscriberInterface to use for auto configuration
 *
 * @see https://github.com/doctrine/DoctrineBundle/issues/674
 */
interface EventSubscriberInterface extends BaseEventSubscriber
{
}
