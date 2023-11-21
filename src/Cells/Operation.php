<?php

namespace Dew\Tablestore\Cells;

class Operation
{
    public const DELETE_ALL_VERSIONS = 0x1;

    public const DELETE_ONE_VERSION = 0x3;

    public const INCREMENT = 0x4;
}
