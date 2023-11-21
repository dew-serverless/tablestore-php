<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\CalculatesChecksum;
use Dew\Tablestore\Contracts\HasValue;

abstract class Cell
{
    /**
     * The name of the cell.
     */
    protected string $name;

    /**
     * The name of the cell.
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Calculate checksum for the cell.
     */
    public function getChecksumBy(CalculatesChecksum $calculator): int
    {
        $checksum = $calculator->string($this->name(), 0);

        if (! $this->shouldChecksumValue()) {
            return $checksum;
        }

        $checksum = $calculator->char($this->type(), $checksum);

        return $this->getValueChecksumBy($calculator, $checksum);
    }

    /**
     * Determine if the cell value should be included in checksum.
     *
     * @phpstan-assert-if-true \Dew\Tablestore\Contracts\HasValue $this
     */
    protected function shouldChecksumValue(): bool
    {
        return $this instanceof HasValue;
    }
}
