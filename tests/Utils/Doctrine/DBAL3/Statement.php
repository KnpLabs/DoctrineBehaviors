<?php

namespace Knp\DoctrineBehaviors\Tests\Utils\Doctrine\DBAL3;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Knp\DoctrineBehaviors\Tests\Utils\Doctrine\DebugStack;

/**
 * @internal
 */
final class Statement extends AbstractStatementMiddleware
{
    public function __construct(
        StatementInterface $statement,
        private DebugStack $debugStack,
        private string $sql,
    ) {
        parent::__construct($statement);
    }

    public function execute($params = null): ResultInterface
    {
        $this->debugStack->startQuery($this->sql);

        try {
            return parent::execute($params);
        } finally {
            $this->debugStack->stopQuery();
        }
    }
}
