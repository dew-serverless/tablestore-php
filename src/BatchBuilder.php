<?php

namespace Dew\Tablestore;

use Protos\Condition;
use Protos\OperationType;
use Protos\RowExistenceExpectation;
use Protos\RowInBatchWriteRowRequest;

class BatchBuilder
{
    /**
     * The operation type.
     */
    protected int $operation;

    /**
     * The scoped primary keys.
     *
     * @var (\Dew\Tablestore\Cells\Cell&\Dew\Tablestore\Contracts\PrimaryKey)[]
     */
    protected array $wheres = [];

    /**
     * The collected cells.
     *
     * @var \Dew\Tablestore\Cells\Cell[]
     */
    protected array $cells = [];

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
     * Insert the row to table.
     *
     * @param  \Dew\Tablestore\Cells\Cell[]  $cells
     */
    public function insert(array $cells): void
    {
        $this->operation = OperationType::PUT;
        $this->cells = $cells;
    }

    /**
     * Modify the existing attributes in table.
     *
     * @param  (\Dew\Tablestore\Cells\Cell&\Dew\Tablestore\Contracts\Attribute)[]  $cells
     */
    public function update(array $cells): void
    {
        $this->operation = OperationType::UPDATE;
        $this->cells = $cells;
    }

    /**
     * Represent the builder as row changes request.
     */
    public function toRequest(): RowInBatchWriteRowRequest
    {
        $row = new RowWriter(new PlainbufferWriter, new Crc);
        $row->writeHeader()->addRow([...$this->wheres, ...$this->cells]);

        $condition = new Condition;
        $condition->setRowExistence(RowExistenceExpectation::IGNORE);

        $request = new RowInBatchWriteRowRequest;
        $request->setType($this->operation);
        $request->setRowChange($row->getBuffer());
        $request->setCondition($condition);

        return $request;
    }

    /**
     * The table name.
     */
    public function getTable(): string
    {
        return $this->table;
    }
}
