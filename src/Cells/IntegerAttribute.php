<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\HasValue;

class IntegerAttribute extends Attribute implements HasValue
{
    use IsIntegerCell;

    /**
     * Set increment operation.
     */
    public function increment(): self
    {
        $this->setOperation(Operation::INCREMENT);

        return $this;
    }
}
