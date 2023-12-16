<?php

namespace Dew\Tablestore\Middlewares;

use Dew\Tablestore\Contracts\BuildsSignature;
use GuzzleHttp\Middleware;

class SignRequest
{
    /**
     * Make a middleware to build signature for the request.
     *
     * @return callable(callable): callable
     */
    public static function make(BuildsSignature $signature): callable
    {
        return Middleware::mapRequest(fn ($request) => $request->withHeader(
            'x-ots-signature', $signature->build($request)
        ));
    }

    /**
     * Make a middleware to build signature for the ACS request.
     *
     * @return callable(callable): callable
     */
    public static function acs(BuildsSignature $signature, string $accessKeyId): callable
    {
        return Middleware::mapRequest(fn ($request) => $request->withHeader(
            'Authorization', sprintf('%s Credential=%s,SignedHeaders=%s,Signature=%s',
                $signature->algorithmForAcs(), $accessKeyId,
                implode(';', $signature->signedHeaders($request)),
                $signature->build($request)
            ))
        );
    }
}
