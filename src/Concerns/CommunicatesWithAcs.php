<?php

namespace Dew\Tablestore\Concerns;

use Dew\Tablestore\Contracts\BuildsSignature;

trait CommunicatesWithAcs
{
    /**
     * The ACS access key ID.
     */
    protected string $accessKeyId;

    /**
     * The ACS access key secret.
     */
    protected string $accessKeySecret;

    /**
     * The service endpoint.
     */
    protected string $endpoint;

    /**
     * The signature builder.
     */
    protected BuildsSignature $signature;

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
     * The service endpoint.
     */
    public function endpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * The signature builder.
     */
    public function signature(): BuildsSignature
    {
        return $this->signature ??= $this->newSignature();
    }

    /**
     * Create a new signature builder.
     */
    abstract protected function newSignature(): BuildsSignature;

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
