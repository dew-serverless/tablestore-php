<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\HasValue;

class DoubleAttribute extends Attribute implements HasValue
{
    use IsDoubleCell;
}
