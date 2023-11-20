<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\HasValue;

class BooleanAttribute extends Attribute implements HasValue
{
    use IsBooleanCell;
}
