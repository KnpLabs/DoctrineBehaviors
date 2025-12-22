<?php

namespace Knp\DoctrineBehaviors\Tests\Utils\Doctrine;

/**
 * @internal
 */
final class DebugStack
{
    /**
     * Executed SQL queries.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $queries = [];

    public ?float $start = null;

    public int $currentQuery = 0;

    public function startQuery(string $sql, ?array $params = null, ?array $types = null): void
    {
        $this->start = microtime(true);

        $this->queries[++$this->currentQuery] = [
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
            'executionMS' => 0,
        ];
    }

    public function stopQuery(): void
    {
        $this->queries[$this->currentQuery]['executionMS'] = microtime(true) - $this->start;
    }

    public function reset(): void
    {
        $this->queries = [];
        $this->currentQuery = 0;
        $this->start = null;
    }
}
