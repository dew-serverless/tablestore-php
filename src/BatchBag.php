<?php

namespace Dew\Tablestore;

class BatchBag
{
    /**
     * The table name and builders pair.
     *
     * @var array<string, \Dew\Tablestore\BatchBuilder[]>
     */
    protected array $tables = [];

    /**
     * Make a new builder for the given table.
     */
    public function table(string $table): BatchBuilder
    {
        return $this->tables[$table][] = (new BatchBuilder)->setTable($table);
    }

    /**
     * Get the table name and builders pair.
     *
     * @return array<string, \Dew\Tablestore\BatchBuilder[]>
     */
    public function getTables(): array
    {
        return $this->tables;
    }
}
