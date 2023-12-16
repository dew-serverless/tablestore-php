<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Contracts\BuildsSignature;
use GuzzleHttp\Psr7\Query;
use Psr\Http\Message\RequestInterface;

class AcsSignature implements BuildsSignature
{
    /**
     * The headers should be included in signature calculation.
     *
     * @var string[]
     */
    public array $whitelist = [];

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
        return 'ACS3-HMAC-SHA256';
    }

    /**
     * The algorithm.
     */
    public function algorithm(): string
    {
        return 'sha256';
    }

    /**
     * Get the headers for signature calculation.
     *
     * @return  string[]
     */
    public function signedHeaders(RequestInterface $request): array
    {
        $signed = [];

        foreach (array_keys($request->getHeaders()) as $header) {
            $header = strtolower($header);

            if ($this->isMetadata($header) || $this->isInWhitelist($header)) {
                $signed[] = $header;
            }
        }

        sort($signed);

        return $signed;
    }

    /**
     * Determine if the header is an ACS metadata.
     */
    public function isMetadata(string $header): bool
    {
        return str_starts_with($header, 'x-acs-');
    }

    /**
     * Determine if the header is in signature whitelist.
     */
    public function isInWhitelist(string $header): bool
    {
        return in_array($header, $this->whitelist, strict: true);
    }

    /**
     * Include the headers to signature calculation.
     *
     * @param  string[]  $headers
     */
    public function include(array $headers): self
    {
        $this->whitelist = $headers;

        return $this;
    }

    /**
     * Build the signature for the given request.
     */
    public function build(RequestInterface $request): string
    {
        $headers = $this->signedHeaders($request);

        $data = implode("\n", [
            $request->getMethod(),
            $request->getUri()->getPath(),
            $this->sortedQuery($request->getUri()->getQuery()),
            implode("\n", array_map(fn ($name): string => sprintf('%s:%s',
                $name, $request->getHeaderLine($name)
            ), $headers)),
            '',
            implode(';', $headers),
            hash('sha256', $request->getBody()->getContents()),
        ]);

        $data = implode("\n", [
            $this->algorithmForAcs(),
            hash('sha256', $data),
        ]);

        return hash_hmac($this->algorithm(), $data, $this->key);
    }

    /**
     * Get the sorted query string.
     */
    protected function sortedQuery(string $query): string
    {
        $parsed = Query::parse($query);

        ksort($parsed);

        return Query::build($parsed);
    }
}
