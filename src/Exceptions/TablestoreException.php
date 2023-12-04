<?php

namespace Dew\Tablestore\Exceptions;

use Exception;
use Protos\Error;
use Psr\Http\Message\ResponseInterface;

class TablestoreException extends Exception
{
    /**
     * Create a Tablestore exception.
     */
    public function __construct(
        protected Error $e
    ) {
        parent::__construct($e->getMessage());
    }

    /**
     * Create a Tablestore exception from error response.
     */
    public static function fromResponse(ResponseInterface $response): self
    {
        $error = new Error;
        $error->mergeFromString($response->getBody()->getContents());

        return new self($error);
    }

    /**
     * Get the error message.
     */
    public function getError(): Error
    {
        return $this->e;
    }
}
