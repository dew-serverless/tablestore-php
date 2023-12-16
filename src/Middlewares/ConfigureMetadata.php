<?php

namespace Dew\Tablestore\Middlewares;

use Dew\Tablestore\Tablestore;
use Dew\Tablestore\TablestoreInstance;
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
        return Middleware::mapRequest(function (RequestInterface $request) use ($tablestore): RequestInterface {
            $request = $request
                ->withHeader('x-ots-accesskeyid', $tablestore->accessKeyId())
                ->withHeader('x-ots-apiversion', '2015-12-31')
                ->withHeader('x-ots-contentmd5', base64_encode(
                    md5($request->getBody()->getContents(), binary: true)
                ))
                ->withHeader('x-ots-date', gmdate('Y-m-d\\TH:i:s.v\\Z'))
                ->withHeader('x-ots-instancename', $tablestore->instanceName());

            if (is_string($tablestore->token())) {
                return $request->withHeader('x-ots-ststoken', $tablestore->token());
            }

            return $request;
        });
    }

    /**
     * Make a middleware to configure ACS metadata.
     *
     * @return callable(callable): callable
     */
    public static function acs(TablestoreInstance $tablestore): callable
    {
        return static fn (callable $handler): callable => static function (
            RequestInterface $request, array $options
        ) use ($handler, $tablestore) {
            $request = $request
                ->withHeader('x-acs-action', $options['acs']['action'])
                ->withHeader('x-acs-date', gmdate('Y-m-d\\TH:i:s\\Z'))
                ->withHeader('x-acs-version', $options['acs']['version']);

            if (is_string($tablestore->token())) {
                $request = $request
                    ->withHeader('x-acs-accesskey-id', $tablestore->accessKeyId())
                    ->withHeader('x-acs-security-token', $tablestore->token());
            }

            unset($options['acs']);

            return $handler($request, $options);
        };
    }
}
