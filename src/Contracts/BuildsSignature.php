<?php

namespace Dew\Tablestore\Contracts;

use Psr\Http\Message\RequestInterface;

interface BuildsSignature
{
    /**
     * The algorithm in ACS format.
     */
    public function algorithmForAcs(): string;

    /**
     * The algorithm.
     */
    public function algorithm(): string;

    /**
     * Get the headers for signature calculation.
     *
     * @return  string[]
     */
    public function signedHeaders(RequestInterface $request): array;

    /**
     * Build the signature for the given request.
     */
    public function build(RequestInterface $request): string;
}
