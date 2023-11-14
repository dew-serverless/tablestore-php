<?php

namespace Dew\Tablestore\Middlewares;

use Dew\Tablestore\Tablestore;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class ConfigureMetadata
{
    /**
     * Make a middleware to configure Tablestore API metadata.
     *
     * @return callable(callable): callable
     */
    public static function make(Tablestore $tablestore): callable
    {
        return Middleware::mapRequest(fn (RequestInterface $request): RequestInterface => $request
            ->withHeader('x-ots-accesskeyid', $tablestore->accessKeyId())
            ->withHeader('x-ots-apiversion', '2015-12-31')
            ->withHeader('x-ots-contentmd5', base64_encode(
                md5($request->getBody()->getContents(), binary: true)
            ))
            ->withHeader('x-ots-date', gmdate('Y-m-d\\TH:i:s.v\\Z'))
            ->withHeader('x-ots-instancename', $tablestore->instanceName())
        );
    }
}
