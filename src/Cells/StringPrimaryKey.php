<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\HasValue;
use Dew\Tablestore\Contracts\PrimaryKey;

class StringPrimaryKey extends Cell implements HasValue, PrimaryKey
{
    use IsStringCell;
}
