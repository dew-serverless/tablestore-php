<?php

namespace Dew\Tablestore\Contracts;

use Psr\Http\Message\RequestInterface;

interface BuildsSignature
{
    /**
     * Build the signature for the given request.
     */
    public function build(RequestInterface $request): string;
}
