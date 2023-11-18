<?php

namespace Dew\Tablestore;

use Protos\Condition;
use Protos\PutRowRequest;
use Protos\PutRowResponse;
use Protos\ReturnContent;
use Protos\ReturnType;
use Protos\RowExistenceExpectation;

class Builder
{
    /**
     * The collected rows.
     *
     * @var \Dew\Tablestore\Cells\Cell[]
     */
    protected array $rows = [];

    /**
     * Create a builder.
     */
    public function __construct(
        protected Tablestore $tablestore,
        protected string $table
    ) {
        //
    }

    /**
     * Insert the rows to table.
     *
     * @param  \Dew\Tablestore\Cells\Cell[]  $rows
     * @return array<string, mixed>
     */
    public function insert(array $rows): array
    {
        $this->rows = $rows;

        return $this->putRow();
    }

    /**
     * Send the put row request to Tablestore.
     *
     * @return array<string, mixed>
     */
    protected function putRow(): array
    {
        $row = $this->rowWriter()->addRow($this->rows);

        $request = new PutRowRequest;
        $request->setTableName($this->table);
        $request->setRow($row->getBuffer());
        $request->setCondition(new Condition([
            'row_existence' => RowExistenceExpectation::IGNORE,
        ]));
        $request->setReturnContent(new ReturnContent([
            'return_column_names' => [],
            'return_type' => ReturnType::RT_PK,
        ]));

        $response = new PutRowResponse;
        $response->mergeFromString(
            $this->tablestore->send('/PutRow', $request)->getBody()->getContents()
        );

        return [
            'consumed' => [
                'capacity_unit' => [
                    'read' => $response->getConsumed()?->getCapacityUnit()?->getRead(),
                    'write' => $response->getConsumed()?->getCapacityUnit()?->getWrite(),
                ],
            ],
            'row' => $this->rowReader($response->getRow())->toArray(),
        ];
    }

    /**
     * The underlying Tablestore instance.
     */
    public function tablestore(): Tablestore
    {
        return $this->tablestore;
    }

    /**
     * The table name.
     */
    public function tableName(): string
    {
        return $this->table;
    }

    /**
     * Make a new row writer.
     */
    protected function rowWriter(): RowWriter
    {
        $row = new RowWriter(new PlainbufferWriter, new Crc);

        return $row->writeHeader();
    }

    /**
     * Make a new row reader.
     */
    protected function rowReader(string $buffer): RowReader
    {
        return new RowReader(new PlainbufferReader($buffer));
    }
}
