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
     * The row existence expectation.
     */
    protected int $expectation = RowExistenceExpectation::IGNORE;

    /**
     * The returned row of the response.
     */
    protected int $returned = ReturnType::RT_PK;

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
     * Expect the row is existing.
     */
    public function expectExists(): self
    {
        return $this->expect(RowExistenceExpectation::EXPECT_EXIST);
    }

    /**
     * Expect the row is missing.
     */
    public function expectMissing(): self
    {
        return $this->expect(RowExistenceExpectation::EXPECT_NOT_EXIST);
    }

    /**
     * Ignore the row existence.
     */
    public function ignoreExistence(): self
    {
        return $this->expect(RowExistenceExpectation::IGNORE);
    }

    /**
     * Set the row existence expectation.
     */
    public function expect(int $expectation): self
    {
        $this->expectation = $expectation;

        return $this;
    }

    /**
     * Set no returned row in response.
     */
    public function withoutReturn(): self
    {
        return $this->returned(ReturnType::RT_NONE);
    }

    /**
     * Return the primary key in response.
     */
    public function returnPrimaryKey(): self
    {
        return $this->returned(ReturnType::RT_PK);
    }

    /**
     * Return the modified attributes in response.
     */
    public function returnModified(): self
    {
        return $this->returned(ReturnType::RT_AFTER_MODIFY);
    }

    /**
     * Set the return type of the response.
     */
    public function returned(int $type): self
    {
        $this->returned = $type;

        return $this;
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
            'row_existence' => $this->expectation,
        ]));
        $request->setReturnContent(new ReturnContent([
            'return_column_names' => [],
            'return_type' => $this->returned,
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
            'row' => $response->getRow() === '' ? null : $this->rowReader($response->getRow())->toArray(),
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
