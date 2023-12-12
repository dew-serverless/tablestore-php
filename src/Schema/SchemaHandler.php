<?php

namespace Dew\Tablestore\Schema;

use Dew\Tablestore\Concerns\InteractsWithRequest;
use Dew\Tablestore\Tablestore;
use InvalidArgumentException;
use Protos\CapacityUnit;
use Protos\CreateTableRequest;
use Protos\CreateTableResponse;
use Protos\DefinedColumnSchema;
use Protos\PrimaryKeySchema;
use Protos\ReservedThroughput;
use Protos\SSESpecification;
use Protos\TableMeta;
use Protos\TableOptions;

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
     * Create a new table.
     */
    public function createTable(string $name, Blueprint $table): CreateTableResponse
    {
        $request = new CreateTableRequest;
        $request->setTableMeta($this->toTableMeta($table)->setTableName($name));
        $request->setReservedThroughput($this->toReservedThroughput($table));
        $request->setTableOptions($this->toTableOptions($table));

        if ($table->encryption instanceof SSESpecification) {
            $request->setSseSpec($table->encryption);
        }

        $response = new CreateTableResponse;
        $response->mergeFromString($this->send('/CreateTable', $request));

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
     * Create a throughput reservations Protobuf message.
     */
    public function toReservedThroughput(Blueprint $table): ReservedThroughput
    {
        return (new ReservedThroughput)->setCapacityUnit(
            (new CapacityUnit)
                ->setRead($table->reservedRead)
                ->setWrite($table->reservedWrite)
        );
    }

    /**
     * Create a table options Protobuf message.
     */
    public function toTableOptions(Blueprint $table): TableOptions
    {
        return (new TableOptions)
            ->setTimeToLive($table->ttl)
            ->setMaxVersions($table->maxVersions)
            ->setDeviationCellVersionInSec($table->versionOffset)
            ->setAllowUpdate($table->allowsUpdate);
    }
}
