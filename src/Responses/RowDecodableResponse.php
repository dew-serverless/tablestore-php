<?php

namespace Dew\Tablestore\Responses;

use BadMethodCallException;
use Dew\Tablestore\Crc;
use Dew\Tablestore\PlainbufferReader;
use Dew\Tablestore\RowReader;

/**
 * @template T of object
 *
 * @mixin T
 */
class RowDecodableResponse
{
    /**
     * Create a row decoded response.
     *
     * @param  T  $response
     */
    public function __construct(
        protected $response
    ) {
        //
    }

    /**
     * The decoded row data.
     *
     * @return array<mixed>|null
     */
    public function getDecodedRow(): ?array
    {
        if (! method_exists($this->response, 'getRow')) {
            throw new BadMethodCallException('Missing getRow method from the response.');
        }

        $buffer = $this->response->getRow();

        if ($buffer === '') {
            return null;
        }

        return $this->reader($buffer)->toArray();
    }

    /**
     * Make a new reader for the given row buffer.
     */
    protected function reader(string $buffer): RowReader
    {
        return new RowReader(new PlainbufferReader($buffer), new Crc);
    }

    /**
     * Proxy calling to the response.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function __call(string $method, array $arguments): mixed
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
