<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: table.proto

namespace Protos;

use UnexpectedValueException;

/**
 * Protobuf type <code>acs.tablestore.table.IndexUpdateMode</code>
 */
class IndexUpdateMode
{
    /**
     * Generated from protobuf enum <code>IUM_ASYNC_INDEX = 0;</code>
     */
    const IUM_ASYNC_INDEX = 0;
    /**
     * Generated from protobuf enum <code>IUM_SYNC_INDEX = 1;</code>
     */
    const IUM_SYNC_INDEX = 1;

    private static $valueToName = [
        self::IUM_ASYNC_INDEX => 'IUM_ASYNC_INDEX',
        self::IUM_SYNC_INDEX => 'IUM_SYNC_INDEX',
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

