<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\HasValue;

class IntegerAttribute extends Attribute implements HasValue
{
    use IsIntegerCell;
}
