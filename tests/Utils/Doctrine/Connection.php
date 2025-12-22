<?php

namespace Knp\DoctrineBehaviors\Tests\Utils\Doctrine;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;

/**
 * @internal
 */
final class Connection extends AbstractConnectionMiddleware
{
    public function __construct(
        ConnectionInterface $connection,
        private DebugStack $debugStack,
    ) {
        parent::__construct($connection);
    }

    public function prepare(string $sql): Statement
    {
        return new Statement(parent::prepare($sql), $this->debugStack, $sql);
    }

    public function query(string $sql): Result
    {
        $this->debugStack->startQuery($sql);

        try {
            return parent::query($sql);
        } finally {
            $this->debugStack->stopQuery();
        }
    }

    public function exec(string $sql): int|string
    {
        $this->debugStack->startQuery($sql);

        try {
            $affectedRows = parent::exec($sql);
        } finally {
            $this->debugStack->stopQuery();
        }

        return $affectedRows;
    }

    public function beginTransaction(): void
    {
        $this->debugStack->startQuery('"START TRANSACTION"');

        try {
            parent::beginTransaction();
        } finally {
            $this->debugStack->stopQuery();
        }
    }

    public function commit(): void
    {
        $this->debugStack->startQuery('"COMMIT"');

        try {
            parent::commit();
        } finally {
            $this->debugStack->stopQuery();
        }
    }

    public function rollBack(): void
    {
        $this->debugStack->startQuery('"ROLLBACK"');

        try {
            parent::rollBack();
        } finally {
            $this->debugStack->stopQuery();
        }
    }
}
