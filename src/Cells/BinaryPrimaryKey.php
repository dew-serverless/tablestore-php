<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\PrimaryKey;

class BinaryPrimaryKey extends Cell implements PrimaryKey
{
    use IsBinaryCell;
}
