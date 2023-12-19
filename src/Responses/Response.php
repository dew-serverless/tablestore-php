<?php

namespace Dew\Tablestore\Responses;

use BadMethodCallException;
use Dew\Tablestore\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * @template TResponse of \Psr\Http\Message\ResponseInterface
 *
 * @mixin TResponse
 */
class Response
{
    /**
     * The decoded data.
     *
     * @var array<mixed>
     */
    protected array $decoded;

    /**
     * Create a new response instance.
     *
     * @param  TResponse  $response
     */
    public function __construct(
        public ResponseInterface $response
    ) {
        //
    }

    /**
     * Get data with dotted notation.
     *
     * @return ($name is null ? array<mixed> : mixed)
     */
    public function json(?string $name = null, mixed $default = null): mixed
    {
        if (! isset($this->decoded)) {
            $data = (string) $this->response->getBody();
            $decoded = json_decode($data, associative: true) ?? [];

            if (! is_array($decoded)) {
                throw new RuntimeException(sprintf(
                    'Failed to decode the content [%s] as JSON data.', $data
                ));
            }

            $this->decoded = $decoded;
        }

        return $name === null ? $this->decoded : Arr::get($this->decoded, $name, $default);
    }

    /**
     * Get the underlying PSR7 Response instance.
     */
    public function toPsr7(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Proxy calling to the response.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $method, array $arguments = []): mixed
    {
        $callback = [$this->response, $method];

        if (is_callable($callback)) {
            return call_user_func_array($callback, $arguments);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()',
            $this->response::class, $method
        ));
    }
}
