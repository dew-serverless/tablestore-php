<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: table.proto

namespace Protos;

use UnexpectedValueException;

/**
 * Protobuf type <code>acs.tablestore.table.SSEKeyType</code>
 */
class SSEKeyType
{
    /**
     * Generated from protobuf enum <code>SSE_KMS_SERVICE = 1;</code>
     */
    const SSE_KMS_SERVICE = 1;
    /**
     * Generated from protobuf enum <code>SSE_BYOK = 2;</code>
     */
    const SSE_BYOK = 2;

    private static $valueToName = [
        self::SSE_KMS_SERVICE => 'SSE_KMS_SERVICE',
        self::SSE_BYOK => 'SSE_BYOK',
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

