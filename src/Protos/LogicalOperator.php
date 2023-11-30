<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: filter.proto

namespace Protos;

use UnexpectedValueException;

/**
 * Protobuf type <code>acs.tablestore.filter.LogicalOperator</code>
 */
class LogicalOperator
{
    /**
     * Generated from protobuf enum <code>LO_NOT = 1;</code>
     */
    const LO_NOT = 1;
    /**
     * Generated from protobuf enum <code>LO_AND = 2;</code>
     */
    const LO_AND = 2;
    /**
     * Generated from protobuf enum <code>LO_OR = 3;</code>
     */
    const LO_OR = 3;

    private static $valueToName = [
        self::LO_NOT => 'LO_NOT',
        self::LO_AND => 'LO_AND',
        self::LO_OR => 'LO_OR',
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
