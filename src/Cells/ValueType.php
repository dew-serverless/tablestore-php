<?php

namespace Dew\Tablestore\Cells;

class ValueType
{
    public const VT_INTEGER = 0x0;

    public const VT_DOUBLE = 0x1;

    public const VT_BOOLEAN = 0x2;

    public const VT_STRING = 0x3;

    public const VT_NULL = 0x6;

    public const VT_BLOB = 0x7;

    public const VT_INF_MIN = 0x9;

    public const VT_INF_MAX = 0xA;

    public const VT_AUTO_INCREMENT = 0xB;
}
