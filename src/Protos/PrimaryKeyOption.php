<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: table.proto

namespace Protos;

use UnexpectedValueException;

/**
 * Protobuf type <code>acs.tablestore.table.PrimaryKeyOption</code>
 */
class PrimaryKeyOption
{
    /**
     * Generated from protobuf enum <code>AUTO_INCREMENT = 1;</code>
     */
    const AUTO_INCREMENT = 1;

    private static $valueToName = [
        self::AUTO_INCREMENT => 'AUTO_INCREMENT',
    ];

    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }


    public static function value($name)
    {
        $const = __CLASS__ . '::' . strtoupper($name);
        if (!defined($const)) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return constant($const);
    }
}
