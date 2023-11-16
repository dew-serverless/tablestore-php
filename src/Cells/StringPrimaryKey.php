<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\PrimaryKey;

class StringPrimaryKey extends Cell implements PrimaryKey
{
    use IsStringCell;
}
