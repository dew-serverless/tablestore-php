<?php

namespace Dew\Tablestore\Schema;

use Dew\Tablestore\Concerns\InteractsWithRequest;
use Dew\Tablestore\Tablestore;
use InvalidArgumentException;
use Protos\CreateTableRequest;
use Protos\CreateTableResponse;
use Protos\DefinedColumnSchema;
use Protos\DeleteTableRequest;
use Protos\DeleteTableResponse;
use Protos\DescribeTableRequest;
use Protos\DescribeTableResponse;
use Protos\ListTableRequest;
use Protos\ListTableResponse;
use Protos\PrimaryKeySchema;
use Protos\ReservedThroughput;
use Protos\SSESpecification;
use Protos\TableMeta;
use Protos\TableOptions;
use Protos\UpdateTableRequest;
use Protos\UpdateTableResponse;

class SchemaHandler
{
    use InteractsWithRequest;

    /**
     * Create a new schema handler.
     */
    public function __construct(
        protected Tablestore $tablestore
    ) {
        //
    }

    /**
     * List all the tables in the instance.
     */
    public function listTable(): ListTableResponse
    {
        $response = new ListTableResponse;
        $response->mergeFromString($this->send('/ListTable', new ListTableRequest));

        return $response;
    }

    /**
     * Get the table information.
     */
    public function getTable(string $table): DescribeTableResponse
    {
        $request = (new DescribeTableRequest)->setTableName($table);

        $response = new DescribeTableResponse;
        $response->mergeFromString($this->send('/DescribeTable', $request));

        return $response;
    }

    /**
     * Create a new table.
     */
    public function createTable(string $name, Blueprint $table): CreateTableResponse
    {
        $request = new CreateTableRequest;
        $request->setTableMeta($this->toTableMeta($table)->setTableName($name));

        if ($table->throughput instanceof ReservedThroughput) {
            $request->setReservedThroughput($table->throughput);
        }

        $request->setTableOptions($this->toTableOptions($table));

        if ($table->encryption instanceof SSESpecification) {
            $request->setSseSpec($table->encryption);
        }

        $response = new CreateTableResponse;
        $response->mergeFromString($this->send('/CreateTable', $request));

        return $response;
    }

    /**
     * Update an existing table.
     */
    public function updateTable(string $name, Blueprint $table): UpdateTableResponse
    {
        $request = (new UpdateTableRequest)->setTableName($name);

        if ($table->throughput instanceof ReservedThroughput) {
            $request->setReservedThroughput($table->throughput);
        }

        if ($this->hasTableOptionsUpdate($table)) {
            $request->setTableOptions($this->toTableOptions($table));
        }

        $response = new UpdateTableResponse;
        $response->mergeFromString($this->send('/UpdateTable', $request));

        return $response;
    }

    /**
     * Delete the existing table.
     */
    public function deleteTable(string $name): DeleteTableResponse
    {
        $request = (new DeleteTableRequest)->setTableName($name);

        $response = new DeleteTableResponse;
        $response->mergeFromString($this->send('/DeleteTable', $request));

        return $response;
    }

    /**
     * Create a table meta Protobuf message.
     */
    public function toTableMeta(Blueprint $table): TableMeta
    {
        [$pks, $cols] = [[], []];

        foreach ($table->columns as $column) {
            $schema = $column->toSchema();

            match ($schema::class) {
                PrimaryKeySchema::class => $pks[] = $schema,
                DefinedColumnSchema::class => $cols[] = $schema,
                default => throw new InvalidArgumentException(sprintf(
                    'Unexpected schema type [%s] is given.', $schema::class
                )),
            };
        }

        return (new TableMeta)
            ->setPrimaryKey($pks)
            ->setDefinedColumn($cols);
    }

    /**
     * Create a table options Protobuf message.
     */
    public function toTableOptions(Blueprint $table): TableOptions
    {
        $options = new TableOptions;

        if (is_int($table->ttl)) {
            $options->setTimeToLive($table->ttl);
        }

        if (is_int($table->maxVersions)) {
            $options->setMaxVersions($table->maxVersions);
        }

        if (is_int($table->versionOffset)) {
            $options->setDeviationCellVersionInSec($table->versionOffset);
        }

        if (is_bool($table->allowsUpdate)) {
            $options->setAllowUpdate($table->allowsUpdate);
        }

        return $options;
    }

    /**
     * Determine if the table options have been modified.
     */
    public function hasTableOptionsUpdate(Blueprint $table): bool
    {
        return $table->ttl !== null
            || $table->maxVersions !== null
            || $table->versionOffset !== null
            || $table->allowsUpdate !== null;
    }
}
