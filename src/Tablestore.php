<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Contracts\BuildsSignature;
use Dew\Tablestore\Middlewares\ConfigureMetadata;
use Dew\Tablestore\Middlewares\SignRequest;
use Google\Protobuf\Internal\Message;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Protos\BatchGetRowResponse;
use Protos\BatchWriteRowResponse;
use Psr\Http\Message\ResponseInterface;

class Tablestore
{
    /**
     * The signature builder.
     */
    protected BuildsSignature $signature;

    /**
     * The instance name.
     */
    protected string $instance;

    /**
     * The STS token for the access key.
     */
    protected string $token;

    /**
     * The HTTP request options.
     *
     * @var array<string, mixed>
     */
    protected array $options = [];

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
     * The ACS access key ID.
     */
    public function accessKeyId(): string
    {
        return $this->accessKeyId;
    }

    /**
     * The ACS access key secret.
     */
    public function accessKeySecret(): string
    {
        return $this->accessKeySecret;
    }

    /**
     * The Tablestore endpoint.
     */
    public function endpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * The Tablestore instance name.
     */
    public function instanceName(): string
    {
        return $this->instance;
    }

    /**
     * The STS token for the access key.
     */
    public function token(): ?string
    {
        return $this->token ?? null;
    }

    /**
     * Configure STS token for the access key.
     */
    public function tokenUsing(string $token): self
    {
        $this->token = $token;

        return $this;
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
        $handler->push(ConfigureMetadata::make($this));
        $handler->push(SignRequest::make($this->signature()));

        $client = new Client(array_merge($this->options(), [
            'base_uri' => $this->endpoint,
            'handler' => $handler,
        ]));

        return $client->post($endpoint, ['body' => $message->serializeToString()]);
    }

    /**
     * The signature builder.
     */
    public function signature(): BuildsSignature
    {
        return $this->signature ??= new TablestoreSignature($this->accessKeySecret);
    }

    /**
     * Set signature builder.
     */
    public function signatureUsing(BuildsSignature $signature): self
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * The HTTP request options.
     *
     * @return array<string, mixed>
     */
    public function options(): array
    {
        $default = [
            'timeout' => 2.0,
        ];

        return array_merge($default, $this->options);
    }

    /**
     * Configure HTTP request options.
     *
     * @param  array<string, mixed>  $options
     */
    public function optionsUsing(array $options): self
    {
        $this->options = $options;

        return $this;
    }
}
