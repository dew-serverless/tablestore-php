<?php

namespace Dew\Tablestore;

use Protos\Condition;
use Protos\PutRowRequest;
use Protos\PutRowResponse;
use Protos\RowExistenceExpectation;

class Builder
{
    /**
     * The collected rows.
     *
     * @var array<int, mixed>
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
     * @param  array<int, mixed>  $rows
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
        $request = new PutRowRequest;
        $request->setTableName($this->table);
        $request->setRow('');
        $request->setCondition(new Condition([
            'row_existence' => RowExistenceExpectation::IGNORE,
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
            'row' => [],
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
}
