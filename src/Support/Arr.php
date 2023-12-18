<?php

namespace Dew\Tablestore\Support;

class Arr
{
    /**
     * Get the data with dotted notation.
     *
     * @param  array<mixed>  $array
     */
    public static function get(array $array, int|string $key, mixed $default = null): mixed
    {
        $key = (string) $key;

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (! str_contains($key, '.')) {
            return $array[$key] ?? $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
