<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Concerns\CommunicatesWithAcs;
use Dew\Tablestore\Contracts\BuildsSignature;
use Dew\Tablestore\Middlewares\ConfigureMetadata;
use Dew\Tablestore\Middlewares\SignRequest;
use Dew\Tablestore\Schema\Blueprint;
use Dew\Tablestore\Schema\SchemaHandler;
use Google\Protobuf\Internal\Message;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Protos\BatchGetRowResponse;
use Protos\BatchWriteRowResponse;
use Protos\CreateTableResponse;
use Protos\DeleteTableResponse;
use Protos\DescribeTableResponse;
use Protos\ListTableResponse;
use Protos\UpdateTableResponse;
use Psr\Http\Message\ResponseInterface;

class Tablestore
{
    use CommunicatesWithAcs;

    /**
     * The instance name.
     */
    protected string $instance;

    /**
     * Create a Tablestore.
     */
    public function __construct(
        protected string $accessKeyId,
        protected string $accessKeySecret,
        protected string $endpoint,
        string $instance = null
    ) {
        $this->instance = $instance ?? GuessInstanceName::make($endpoint);
    }

    /**
     * The Tablestore instance name.
     */
    public function instanceName(): string
    {
        return $this->instance;
    }

    /**
     * List all the tables in the instance.
     */
    public function listTable(): ListTableResponse
    {
        return (new SchemaHandler($this))->listTable();
    }

    /**
     * Get the table information.
     */
    public function getTable(string $table): DescribeTableResponse
    {
        return (new SchemaHandler($this))->getTable($table);
    }

    /**
     * Determine whether the table exists.
     */
    public function hasTable(string $table): bool
    {
        return (new SchemaHandler($this))->hasTable($table);
    }

    /**
     * Create a new table.
     *
     * @param  callable(\Dew\Tablestore\Schema\Blueprint): void  $callback
     */
    public function createTable(string $table, callable $callback): CreateTableResponse
    {
        $blueprint = (new Blueprint)
            ->reserveRead(0)
            ->reserveWrite(0)
            ->forever()
            ->maxVersions(1);

        $callback($blueprint);

        return (new SchemaHandler($this))->createTable($table, $blueprint);
    }

    /**
     * Update an existing table.
     *
     * @param  callable(\Dew\Tablestore\Schema\Blueprint): void  $callback
     */
    public function updateTable(string $table, callable $callback): UpdateTableResponse
    {
        $callback($blueprint = new Blueprint);

        return (new SchemaHandler($this))->updateTable($table, $blueprint);
    }

    /**
     * Delete the existing table.
     */
    public function deleteTable(string $table): DeleteTableResponse
    {
        return (new SchemaHandler($this))->deleteTable($table);
    }

    /**
     * Create a builder against the given table.
     */
    public function table(string $table): Builder
    {
        return (new Builder)
            ->setTable($table)
            ->handlerUsing(new Handler($this));
    }

    /**
     * Create a builder for multiple data manipulation.
     *
     * @param  callable(\Dew\Tablestore\BatchBag): void  $callback
     */
    public function batch(callable $callback): BatchGetRowResponse|BatchWriteRowResponse
    {
        $callback($bag = new BatchBag);

        return (new BatchHandler($this))->handle($bag);
    }

    /**
     * Send the HTTP request.
     */
    public function send(string $endpoint, Message $message): ResponseInterface
    {
        $handler = HandlerStack::create();
        $handler->push(ConfigureMetadata::forOts($this));
        $handler->push(SignRequest::make($this->signature()));

        $client = new Client(array_merge($this->options(), [
            'base_uri' => $this->endpoint,
            'handler' => $handler,
        ]));

        return $client->post($endpoint, ['body' => $message->serializeToString()]);
    }

    /**
     * Create a new signature builder.
     */
    protected function newSignature(): BuildsSignature
    {
        return new TablestoreSignature($this->accessKeySecret);
    }
}
