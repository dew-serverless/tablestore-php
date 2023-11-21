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
     * Represent the builder as row changes request.
     */
    public function toRequest(): RowInBatchWriteRowRequest
    {
        $row = new RowWriter(new PlainbufferWriter, new Crc);
        $row->writeHeader()->addRow($this->cells);

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
