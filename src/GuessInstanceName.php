<?php

namespace Dew\Tablestore;

use InvalidArgumentException;

class GuessInstanceName
{
    /**
     * Guess instance name from the endpoint.
     */
    public static function make(string $endpoint): string
    {
        $hostname = parse_url($endpoint, PHP_URL_HOST);

        if ($hostname === false || $hostname === null) {
            throw new InvalidArgumentException(sprintf(
                'Could not resolve the instance from the endpoint [%s].', $endpoint
            ));
        }

        return explode('.', $hostname)[0];
    }
}
