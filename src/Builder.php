<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Responses\RowDecodableResponse;
use Google\Protobuf\Internal\Message;
use Protos\Condition;
use Protos\GetRowRequest;
use Protos\GetRowResponse;
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
     * The list of column names to retrieve with.
     *
     * @var string[]
     */
    protected array $selects = [];

    /**
     * The scoped primary keys.
     *
     * @var \Dew\Tablestore\Cells\Cell[]
     */
    protected array $wheres = [];

    /**
     * The maximal value versions retrieval.
     */
    protected int $takes = 1;

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
     * Select a list of column name to retrieve with.
     *
     * @param  string[]  $columns
     */
    public function select(array $columns = []): self
    {
        $this->selects = $columns;

        return $this;
    }

    /**
     * Filter rows by the given primary keys.
     *
     * @param  \Dew\Tablestore\Cells\Cell[]  $primaryKeys
     */
    public function where(array $primaryKeys): self
    {
        $this->wheres = $primaryKeys;

        return $this;
    }

    /**
     * Set the maximal value versions to retrieve.
     */
    public function take(int $versions): self
    {
        $this->takes = $versions;

        return $this;
    }

    /**
     * Insert the rows to table.
     *
     * @param  \Dew\Tablestore\Cells\Cell[]  $rows
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\PutRowResponse>
     */
    public function insert(array $rows): RowDecodableResponse
    {
        $this->rows = $rows;

        return $this->putRow();
    }

    /**
     * Query rows from table.
     *
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\GetRowResponse>
     */
    public function get(): RowDecodableResponse
    {
        return $this->getRow();
    }

    /**
     * Send the put row request to Tablestore.
     *
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\PutRowResponse>
     */
    protected function putRow(): RowDecodableResponse
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
        $response->mergeFromString($this->tablestore->send('/PutRow', $request)->getBody()->getContents());

        return new RowDecodableResponse($response);
    }

    /**
     * Send the get row request to Tablestore.
     *
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\GetRowResponse>
     */
    protected function getRow(): RowDecodableResponse
    {
        $row = $this->rowWriter()->addRow($this->wheres);

        $request = new GetRowRequest;
        $request->setTableName($this->table);
        $request->setPrimaryKey($row->getBuffer());
        $request->setColumnsToGet($this->selects);
        $request->setMaxVersions($this->takes);

        $response = new GetRowResponse;
        $response->mergeFromString($this->tablestore->send('/GetRow', $request)->getBody()->getContents());

        return new RowDecodableResponse($response);
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
}
