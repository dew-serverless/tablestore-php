<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\HasValue;

class StringAttribute extends Attribute implements HasValue
{
    use IsStringCell;
}
