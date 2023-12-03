<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Concerns\HasConditions;
use Dew\Tablestore\Responses\RowDecodableResponse;

class Builder
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
     * The builder handler.
     */
    protected Handler $handler;

    /**
     * Insert the rows to table.
     *
     * @param  \Dew\Tablestore\Cells\Cell[]  $rows
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\PutRowResponse>
     */
    public function insert(array $rows): RowDecodableResponse
    {
        $this->row = $this->newRow()->addRow($rows);

        return $this->handler()->putRow($this);
    }

    /**
     * Modify the existing attributes in table.
     *
     * @param  \Dew\Tablestore\Cells\Cell[]  $attributes
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\UpdateRowResponse>
     */
    public function update(array $attributes): RowDecodableResponse
    {
        $this->row = $this->newRow()->addRow([...$this->whereKeys, ...$attributes]);

        return $this->handler()->updateRow($this);
    }

    /**
     * Remove the row from table.
     *
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\DeleteRowResponse>
     */
    public function delete(): RowDecodableResponse
    {
        $this->row = $this->newRow()->deleteRow($this->whereKeys);

        return $this->handler()->deleteRow($this);
    }

    /**
     * Query rows from table.
     *
     * @return \Dew\Tablestore\Responses\RowDecodableResponse<\Protos\GetRowResponse>
     */
    public function get(): RowDecodableResponse
    {
        $this->row = $this->newRow()->addRow($this->whereKeys);

        return $this->handler()->getRow($this);
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
     * Make a new row writer.
     */
    protected function newRow(): RowWriter
    {
        $row = new RowWriter(new PlainbufferWriter, new Crc);

        return $row->writeHeader();
    }

    /**
     * Get the builder handler.
     */
    public function handler(): Handler
    {
        return $this->handler;
    }

    /**
     * Configure handler for the builder.
     */
    public function handlerUsing(Handler $handler): self
    {
        $this->handler = $handler;

        return $this;
    }
}
