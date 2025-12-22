<?php

namespace Knp\DoctrineBehaviors\Tests\Utils\Doctrine;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

/**
 * @internal
 */
final class Driver extends AbstractDriverMiddleware
{
    public function __construct(
        DriverInterface $driver,
        private DebugStack $debugStack,
    ) {
        parent::__construct($driver);
    }

    public function connect(array $params): ConnectionInterface
    {
        $connection = parent::connect($params);

        if ((string) (new \ReflectionMethod(ConnectionInterface::class, 'commit'))->getReturnType() !== 'void') {
            return new DBAL3\Connection($connection, $this->debugStack);
        }

        return new Connection($connection, $this->debugStack);
    }
}
