<?php

namespace Dew\Tablestore;

use Protos\BatchGetRowRequest;
use Protos\BatchGetRowResponse;
use Protos\BatchWriteRowRequest;
use Protos\BatchWriteRowResponse;
use Protos\RowInBatchWriteRowRequest;
use Protos\TableInBatchGetRowRequest;
use Protos\TableInBatchWriteRowRequest;

class BatchManager
{
    /**
     * Create a batch manager.
     */
    public function __construct(
        protected Tablestore $tablestore,
        protected BatchChanges $bag
    ) {
        //
    }

    /**
     * Get the rows.
     */
    public function read(): BatchGetRowResponse
    {
        $request = new BatchGetRowRequest;
        $request->setTables($this->buildTablesForRead());

        $response = new BatchGetRowResponse;
        $response->mergeFromString(
            $this->tablestore->send('/BatchGetRow', $request)->getBody()->getContents()
        );

        return $response;
    }

    /**
     * Apply the changes.
     */
    public function write(): BatchWriteRowResponse
    {
        $request = new BatchWriteRowRequest;
        $request->setTables($this->buildTables());

        $response = new BatchWriteRowResponse;
        $response->mergeFromString(
            $this->tablestore->send('/BatchWriteRow', $request)->getBody()->getContents()
        );

        return $response;
    }

    /**
     * Build tables for retrieving multiple rows.
     *
     * @return \Protos\TableInBatchGetRowRequest[]
     */
    protected function buildTablesForRead(): array
    {
        $tables = [];

        foreach ($this->bag->getTables() as $table => $changes) {
            $request = new TableInBatchGetRowRequest;
            $request->setTableName($table);
            $request->setPrimaryKey(array_map(fn ($builder): string => $builder->getRow()->getBuffer(), $changes));
            $request->setMaxVersions(1);

            $tables[] = $request;
        }

        return $tables;
    }

    /**
     * Build table changes requests from the changes bag.
     *
     * @return \Protos\TableInBatchWriteRowRequest[]
     */
    protected function buildTables(): array
    {
        $tables = [];

        foreach ($this->bag->getTables() as $table => $changes) {
            $request = new TableInBatchWriteRowRequest;
            $request->setTableName($table);
            $request->setRows(array_map(fn ($builder): RowInBatchWriteRowRequest => $builder->toRequest(), $changes));

            $tables[] = $request;
        }

        return $tables;
    }
}
