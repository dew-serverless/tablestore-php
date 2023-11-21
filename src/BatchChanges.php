<?php

namespace Dew\Tablestore;

class BatchChanges
{
    /**
     * The table name and changes pair.
     *
     * @var array<string, \Dew\Tablestore\BatchBuilder[]>
     */
    protected array $tables = [];

    /**
     * Propose data changes against the given table.
     */
    public function table(string $table): BatchBuilder
    {
        return $this->tables[$table][] = new BatchBuilder($table);
    }

    /**
     * Get the table name and changes pair.
     *
     * @return array<string, \Dew\Tablestore\BatchBuilder[]>
     */
    public function getTables(): array
    {
        return $this->tables;
    }
}
