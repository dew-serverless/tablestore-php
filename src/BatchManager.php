<?php

namespace Dew\Tablestore;

use Protos\BatchWriteRowRequest;
use Protos\BatchWriteRowResponse;
use Protos\RowInBatchWriteRowRequest;
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
