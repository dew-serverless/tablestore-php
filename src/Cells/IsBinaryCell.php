<?php

namespace Dew\Tablestore\Cells;

trait IsBinaryCell
{
    use IsStringCell;

    /**
     * The value type of the cell.
     */
    public function type(): int
    {
        return ValueType::VT_BLOB;
    }
}
