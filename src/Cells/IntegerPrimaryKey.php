<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\PrimaryKey;

class IntegerPrimaryKey extends Cell implements PrimaryKey
{
    use IsIntegerCell;
}
