<?php

namespace Dew\Tablestore\Cells;

class Tag
{
    public const HEADER = 0x75;

    public const PK = 0x1;

    public const ATTR = 0x2;

    public const CELL = 0x3;

    public const CELL_NAME = 0x4;

    public const CELL_VALUE = 0x5;

    public const CELL_OP = 0x6;

    public const CELL_TS = 0x7;

    public const DELETE_MARKER = 0x8;

    public const ROW_CHECKSUM = 0x9;

    public const CELL_CHECKSUM = 0xA;
}
