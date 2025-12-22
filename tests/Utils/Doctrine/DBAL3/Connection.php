<?php

namespace Knp\DoctrineBehaviors\Tests\Utils\Doctrine\DBAL3;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Knp\DoctrineBehaviors\Tests\Utils\Doctrine\DebugStack;

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

    public function exec(string $sql): int
    {
        $this->debugStack->startQuery($sql);

        try {
            return parent::exec($sql);
        } finally {
            $this->debugStack->stopQuery();
        }
    }

    public function beginTransaction(): bool
    {
        $this->debugStack->startQuery('"START TRANSACTION"');

        try {
            return parent::beginTransaction();
        } finally {
            $this->debugStack->stopQuery();
        }
    }

    public function commit(): bool
    {
        $this->debugStack->startQuery('"COMMIT"');

        try {
            return parent::commit();
        } finally {
            $this->debugStack->stopQuery();
        }
    }

    public function rollBack(): bool
    {
        $this->debugStack->startQuery('"ROLLBACK"');

        try {
            return parent::rollBack();
        } finally {
            $this->debugStack->stopQuery();
        }
    }
}
