<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Concerns\FilterRows;
use Protos\Condition;
use Protos\OperationType;
use Protos\RowExistenceExpectation;
use Protos\RowInBatchWriteRowRequest;

class BatchBuilder
{
    use FilterRows;

    /**
     * The operation type.
     */
    protected int $operation;

    /**
     * The row writer.
     */
    protected RowWriter $row;

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
        unset($this->operation);
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
     * Represent the builder as row changes request.
     */
    public function toWriteRequest(): RowInBatchWriteRowRequest
    {
        $condition = new Condition;
        $condition->setRowExistence(RowExistenceExpectation::IGNORE);

        $request = new RowInBatchWriteRowRequest;
        $request->setType($this->operation);
        $request->setRowChange($this->row->getBuffer());
        $request->setCondition($condition);

        return $request;
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
     * The row writer.
     */
    public function getRow(): RowWriter
    {
        return $this->row;
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
        return ! $this->isWrite();
    }

    /**
     * Determine if the builder belongs to write batch.
     */
    public function isWrite(): bool
    {
        return isset($this->operation);
    }
}
