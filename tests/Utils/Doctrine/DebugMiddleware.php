<?php

namespace Knp\DoctrineBehaviors\Tests\Utils\Doctrine;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware;

/**
 * @internal
 */
final class DebugMiddleware implements Middleware
{
    public function __construct(
        private DebugStack $debugStack,
    ) {
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new Driver($driver, $this->debugStack);
    }
}
