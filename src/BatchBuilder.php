<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Concerns\HasConditions;
use Protos\OperationType;

class BatchBuilder
{
    use HasConditions;

    /**
     * The table name.
     */
    public string $table;

    /**
     * The row writer.
     */
    public RowWriter $row;

    /**
     * The operation type.
     */
    public ?int $operation = null;

    /**
     * Query rows from table.
     */
    public function get(): void
    {
        $this->operation = null;
        $this->row = $this->newRow()->addRow($this->whereKeys);
    }

    /**
     * Insert the row to table.
     *
     * @param  \Dew\Tablestore\Cells\Cell[]  $cells
     */
    public function insert(array $cells): void
    {
        $this->operation = OperationType::PUT;
        $this->row = $this->newRow()->addRow($cells);
    }

    /**
     * Modify the existing attributes in table.
     *
     * @param  \Dew\Tablestore\Cells\Cell[]  $attributes
     */
    public function update(array $attributes): void
    {
        $this->operation = OperationType::UPDATE;
        $this->row = $this->newRow()->addRow([...$this->whereKeys, ...$attributes]);
    }

    /**
     * Remove the row from table.
     */
    public function delete(): void
    {
        $this->operation = OperationType::DELETE;
        $this->row = $this->newRow()->deleteRow($this->whereKeys);
    }

    /**
     * Make a new row writer.
     */
    protected function newRow(): RowWriter
    {
        $row = new RowWriter(new PlainbufferWriter, new Crc);

        return $row->writeHeader();
    }

    /**
     * Set the table name.
     */
    public function setTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get the table name.
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Determine if the builder belongs to read batch.
     */
    public function isRead(): bool
    {
        return $this->operation === null;
    }

    /**
     * Determine if the builder belongs to write batch.
     */
    public function isWrite(): bool
    {
        return ! $this->isRead();
    }
}
