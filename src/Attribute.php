<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Cells\BinaryAttribute;
use Dew\Tablestore\Cells\BooleanAttribute;
use Dew\Tablestore\Cells\DoubleAttribute;
use Dew\Tablestore\Cells\IntegerAttribute;
use Dew\Tablestore\Cells\StringAttribute;
use Dew\Tablestore\Cells\ValueType;
use InvalidArgumentException;

class Attribute
{
    /**
     * Create an integer attribute.
     */
    public static function integer(string $name, int $value): IntegerAttribute
    {
        return new IntegerAttribute($name, $value);
    }

    /**
     * Create a double attribute.
     */
    public static function double(string $name, float $value): DoubleAttribute
    {
        return new DoubleAttribute($name, $value);
    }

    /**
     * Create a boolean attribute.
     */
    public static function boolean(string $name, bool $value): BooleanAttribute
    {
        return new BooleanAttribute($name, $value);
    }

    /**
     * Create a string attribute.
     */
    public static function string(string $name, string $value): StringAttribute
    {
        return new StringAttribute($name, $value);
    }

    /**
     * Create a binary attribute.
     */
    public static function binary(string $name, string $value): BinaryAttribute
    {
        return new BinaryAttribute($name, $value);
    }

    /**
     * Get the attribute class by the given type.
     *
     * @return class-string<\Dew\Tablestore\Cells\Cell>
     */
    public static function classFromType(int $type): string
    {
        return match ($type) {
            ValueType::VT_INTEGER => IntegerAttribute::class,
            ValueType::VT_DOUBLE => DoubleAttribute::class,
            ValueType::VT_BOOLEAN => BooleanAttribute::class,
            ValueType::VT_STRING => StringAttribute::class,
            ValueType::VT_BLOB => BinaryAttribute::class,
            default => throw new InvalidArgumentException("Unexpected attribute type [$type] given."),
        };
    }
}
