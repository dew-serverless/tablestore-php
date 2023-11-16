<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Cells\BinaryPrimaryKey;
use Dew\Tablestore\Cells\IntegerPrimaryKey;
use Dew\Tablestore\Cells\StringPrimaryKey;
use Dew\Tablestore\Cells\ValueType;
use InvalidArgumentException;

class PrimaryKey
{
    /**
     * Create an integer primary key.
     */
    public static function integer(string $key, int $value): IntegerPrimaryKey
    {
        return new IntegerPrimaryKey($key, $value);
    }

    /**
     * Create a string primary key.
     */
    public static function string(string $key, string $value): StringPrimaryKey
    {
        return new StringPrimaryKey($key, $value);
    }

    /**
     * Create a binary primary key.
     */
    public static function binary(string $key, string $value): BinaryPrimaryKey
    {
        return new BinaryPrimaryKey($key, $value);
    }

    /**
     * Get the primary key class by the given type.
     *
     * @return class-string<\Dew\Tablestore\Cells\Cell>
     */
    public static function classFromType(int $type): string
    {
        return match ($type) {
            ValueType::VT_INTEGER => IntegerPrimaryKey::class,
            ValueType::VT_STRING => StringPrimaryKey::class,
            ValueType::VT_BLOB => BinaryPrimaryKey::class,
            default => throw new InvalidArgumentException("Unexpected primary key type [$type] given."),
        };
    }
}
