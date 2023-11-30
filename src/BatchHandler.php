<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Concerns\InteractsWithRequest;
use Dew\Tablestore\Exceptions\BatchHandlerException;
use Google\Protobuf\Internal\Message;
use Protos\BatchGetRowRequest;
use Protos\BatchGetRowResponse;
use Protos\BatchWriteRowRequest;
use Protos\BatchWriteRowResponse;
use Protos\Filter;
use Protos\RowInBatchWriteRowRequest;
use Protos\TableInBatchGetRowRequest;
use Protos\TableInBatchWriteRowRequest;

class BatchHandler
{
    use InteractsWithRequest;

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

        throw new BatchHandlerException('Requires something in a batch.');
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
            [$pks, $selects, $takes, $filter] = $this->extractPayloadFromRead($builders);

            $request = (new TableInBatchGetRowRequest)
                ->setTableName($table)
                ->setPrimaryKey($pks)
                ->setColumnsToGet($selects)
                ->setMaxVersions($takes);

            if ($filter instanceof Filter) {
                $request->setFilter($filter->serializeToString());
            }

            $tables[] = $request;
        }

        return $tables;
    }

    /**
     * Extract payload from a list of read builders.
     *
     * @param  \Dew\Tablestore\BatchBuilder[]  $builders
     * @return array{0: string[], 1: string[], 2: positive-int, 3: \Protos\Filter|null}
     */
    protected function extractPayloadFromRead(array $builders): array
    {
        $payload = array_reduce($builders, function (array $carry, BatchBuilder $builder): array {
            if ($builder->isWrite()) {
                throw new BatchHandlerException('Could not mix read and write operations in one batch.');
            }

            if (isset($builder->row)) {
                // pks: combine the buffer in each builder.
                $carry[0][] = $builder->row->getBuffer();
            }

            // selects: combine the selected columns in each builder.
            $carry[1] = [...$carry[1], ...$builder->selects];

            // takes: retrieve the maximal value version from builders.
            $carry[2] = max($carry[2], $builder->maxVersions);

            // filter: override with the last occurrence of the row filter.
            $carry[3] = $this->shouldBuildFilter($builder)
                ? $this->buildFilter($builder)
                : $carry[3];

            return $carry;
        }, [[], [], 0, null]);

        // Primary keys are required to retrieve rows from a table.
        if ($payload[0] === []) {
            throw new BatchHandlerException('The statement is incomplete.');
        }

        return $payload;
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
                ->setRows(array_map(fn ($builder): RowInBatchWriteRowRequest => $this->toChangesRequest($builder), $builders));
        }

        return $tables;
    }

    /**
     * Build a row changes request from the given builder.
     */
    protected function toChangesRequest(BatchBuilder $builder): RowInBatchWriteRowRequest
    {
        if (! isset($builder->row)) {
            throw new BatchHandlerException('The statement is incomplete.');
        }

        $buffer = $builder->row->getBuffer();

        if ($builder->isRead()) {
            throw new BatchHandlerException('Could not mix read and write operations in one batch.');
        }

        return (new RowInBatchWriteRowRequest)
            ->setType($builder->operation ?? throw new BatchHandlerException('The statement is incomplete.'))
            ->setRowChange($buffer)
            ->setCondition($this->toCondition($builder))
            ->setReturnContent($this->toReturnContent($builder));
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
