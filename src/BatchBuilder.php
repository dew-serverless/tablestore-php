<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Concerns\HasConditions;
use Protos\OperationType;

class BatchBuilder
{
    use HasConditions;

    /**
     * The operation type.
     */
    public ?int $operation = null;

    /**
     * The row writer.
     */
    public ?RowWriter $row = null;

    /**
     * Create a batch builder.
     */
    public function __construct(
        protected string $table
    ) {
        //
    }

    /**
     * Filter rows by the given primary keys.
     *
     * @param  (\Dew\Tablestore\Cells\Cell&\Dew\Tablestore\Contracts\PrimaryKey)[]  $primaryKeys
     */
    public function where(array $primaryKeys): self
    {
        $this->wheres = $primaryKeys;

        return $this;
    }

    /**
     * Query rows from table.
     */
    public function get(): void
    {
        $this->operation = null;
        $this->row = $this->newRow()->addRow($this->wheres);
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
     * @param  (\Dew\Tablestore\Cells\Cell&\Dew\Tablestore\Contracts\Attribute)[]  $attributes
     */
    public function update(array $attributes): void
    {
        $this->operation = OperationType::UPDATE;
        $this->row = $this->newRow()->addRow([...$this->wheres, ...$attributes]);
    }

    /**
     * Remove the row from table.
     */
    public function delete(): void
    {
        $this->operation = OperationType::DELETE;
        $this->row = $this->newRow()->deleteRow($this->wheres);
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
     * The table name.
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
