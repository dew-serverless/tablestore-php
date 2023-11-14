<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Contracts\BuildsSignature;
use Psr\Http\Message\RequestInterface;

class TablestoreSignature implements BuildsSignature
{
    /**
     * Create a Tablestore signature builder.
     */
    public function __construct(
        protected string $key
    ) {
        //
    }

    /**
     * Build the signature for the given request.
     */
    public function build(RequestInterface $request): string
    {
        $data = implode("\n", [
            $request->getUri()->getPath(),
            $request->getMethod(),
            '',
            $this->contextFrom($request),
            '',
        ]);

        return base64_encode(
            hash_hmac('sha1', $data, $this->key, binary: true)
        );
    }

    /**
     * Extract context from the given request.
     */
    protected function contextFrom(RequestInterface $request): string
    {
        $headers = $request->getHeaders();

        $headers = array_filter($headers, fn ($header): bool => $this->isMetadataHeader($header), ARRAY_FILTER_USE_KEY);

        $headers = array_map(
            fn ($value, $header): string => sprintf('%s:%s', $header, end($value)),
            array_values($headers), array_keys($headers)
        );

        return implode("\n", $headers);
    }

    /**
     * Determine if the header is a Tablestore metadata field.
     */
    protected function isMetadataHeader(string $header): bool
    {
        return str_starts_with($header, 'x-ots-');
    }
}
