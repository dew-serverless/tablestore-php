<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\HasValue;

class BinaryAttribute extends Attribute implements HasValue
{
    use IsBinaryCell;
}
