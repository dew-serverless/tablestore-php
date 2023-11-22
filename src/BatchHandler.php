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
    public function buildReadTables(BatchBag $bag): array
    {
        $tables = [];

        foreach ($bag->getTables() as $table => $builders) {
            [$pks, $selects, $takes] = array_reduce($builders, function (array $carry, $builder): array {
                if ($builder->isWrite()) {
                    throw new BatchHandlerException('Could not mix read and write operations in one batch.');
                }

                // pks: combine the buffer in each builder.
                $carry[0][] = $builder->getRow()->getBuffer();

                // selects: combine the selected columns in each builder.
                $carry[1] = [...$carry[1], ...$builder->selects];

                // takes: retrieve the maximal value version from builders.
                $carry[2] = max($carry[2], $builder->takes);

                return $carry;
            }, [[], [], 0]);

            $tables[] = (new TableInBatchGetRowRequest)
                ->setTableName($table)
                ->setPrimaryKey($pks)
                ->setColumnsToGet($selects)
                ->setMaxVersions($takes);
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
    public function buildWriteTables(BatchBag $bag): array
    {
        $tables = [];

        foreach ($bag->getTables() as $table => $builders) {
            $tables[] = (new TableInBatchWriteRowRequest)
                ->setTableName($table)
                ->setRows(array_map(function ($builder): RowInBatchWriteRowRequest {
                    if ($builder->isRead()) {
                        throw new BatchHandlerException('Could not mix read and write operations in one batch.');
                    }

                    return $builder->toWriteRequest();
                }, $builders));
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
