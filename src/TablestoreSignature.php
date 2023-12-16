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
     * The algorithm in ACS format.
     */
    public function algorithmForAcs(): string
    {
        return $this->algorithm();
    }

    /**
     * The algorithm.
     */
    public function algorithm(): string
    {
        return 'sha1';
    }

    /**
     * Get the headers for signature calculation.
     *
     * @return  string[]
     */
    public function signedHeaders(RequestInterface $request): array
    {
        return array_filter(array_keys($request->getHeaders()),
            fn ($header): bool => $this->isMetadataHeader($header)
        );
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
            implode("\n", array_map(fn ($name): string => sprintf('%s:%s',
                $name, $request->getHeaderLine($name)
            ), $this->signedHeaders($request))),
            '',
        ]);

        return base64_encode(
            hash_hmac($this->algorithm(), $data, $this->key, binary: true)
        );
    }

    /**
     * Determine if the header is a Tablestore metadata field.
     */
    protected function isMetadataHeader(string $header): bool
    {
        return str_starts_with($header, 'x-ots-');
    }
}
