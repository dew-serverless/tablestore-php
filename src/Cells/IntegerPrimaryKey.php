<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\HasValue;
use Dew\Tablestore\Contracts\PrimaryKey;

class IntegerPrimaryKey extends Cell implements HasValue, PrimaryKey
{
    use IsIntegerCell;
}
