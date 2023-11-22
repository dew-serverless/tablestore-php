<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Exceptions\BatchHandlerException;
use Google\Protobuf\Internal\Message;
use Protos\BatchGetRowRequest;
use Protos\BatchGetRowResponse;
use Protos\BatchWriteRowRequest;
use Protos\BatchWriteRowResponse;
use Protos\RowInBatchWriteRowRequest;
use Protos\TableInBatchGetRowRequest;
use Protos\TableInBatchWriteRowRequest;

class BatchHandler
{
    /**
     * Create a new batch handler.
     */
    public function __construct(
        protected Tablestore $tablestore
    ) {
        //
    }

    /**
     * Handle the batch bag.
     */
    public function handle(BatchBag $bag): BatchGetRowResponse|BatchWriteRowResponse
    {
        return $this->isReadBatch($bag) ? $this->read($bag) : $this->write($bag);
    }

    /**
     * Determine if the bag is a read batch.
     */
    protected function isReadBatch(BatchBag $bag): bool
    {
        foreach ($bag->getTables() as $builders) {
            return $builders[0]->isRead();
        }

        throw new BatchHandlerException('Requires something in batch API.');
    }

    /**
     * Get multiple rows with the given builders.
     */
    protected function read(BatchBag $bag): BatchGetRowResponse
    {
        $request = new BatchGetRowRequest;
        $request->setTables($this->buildReadTables($bag));

        $response = new BatchGetRowResponse;
        $response->mergeFromString($this->send('/BatchGetRow', $request));

        return $response;
    }

    /**
     * Build retrieval tables from the given bag.
     *
     * @return \Protos\TableInBatchGetRowRequest[]
     */
    protected function buildReadTables(BatchBag $bag): array
    {
        $tables = [];

        foreach ($bag->getTables() as $table => $builders) {
            $request = new TableInBatchGetRowRequest;
            $request->setTableName($table);
            $request->setPrimaryKey(array_map(fn ($builder): string => $builder->getRow()->getBuffer(), $builders));
            $request->setMaxVersions(1);

            $tables[] = $request;
        }

        return $tables;
    }

    /**
     * Apply the changes from the given builders.
     */
    protected function write(BatchBag $bag): BatchWriteRowResponse
    {
        $request = new BatchWriteRowRequest;
        $request->setTables($this->buildWriteTables($bag));

        $response = new BatchWriteRowResponse;
        $response->mergeFromString($this->send('/BatchWriteRow', $request));

        return $response;
    }

    /**
     * Build changes tables from the given bag.
     *
     * @return \Protos\TableInBatchWriteRowRequest[]
     */
    protected function buildWriteTables(BatchBag $bag): array
    {
        $tables = [];

        foreach ($bag->getTables() as $table => $builders) {
            $request = new TableInBatchWriteRowRequest;
            $request->setTableName($table);
            $request->setRows(array_map(fn ($builder): RowInBatchWriteRowRequest => $builder->toWriteRequest(), $builders));

            $tables[] = $request;
        }

        return $tables;
    }

    /**
     * Communicate with Tablestore with the given message.
     */
    protected function send(string $endpoint, Message $message): string
    {
        return $this->tablestore->send($endpoint, $message)
            ->getBody()
            ->getContents();
    }
}
