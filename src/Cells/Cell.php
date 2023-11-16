<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\CalculatesChecksum;
use Dew\Tablestore\PlainbufferReader;
use Dew\Tablestore\PlainbufferWriter;

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
     * The value of the cell.
     */
    abstract public function value(): mixed;

    /**
     * The value type of the cell.
     */
    abstract public function type(): int;

    /**
     * Get value from the formatted value in buffer.
     */
    abstract public static function fromFormattedValue(PlainbufferReader $buffer): mixed;

    /**
     * Build formatted value to buffer.
     *
     * formatted_value = value_type value_len value_data
     * value_type = int8
     * value_len = int32
     */
    abstract public function toFormattedValue(PlainbufferWriter $buffer): void;

    /**
     * Calculate checksum for the cell value.
     */
    abstract public function getValueChecksumBy(CalculatesChecksum $calculator, int $checksum): int;

    /**
     * Calculate checksum for the cell.
     */
    public function getChecksumBy(CalculatesChecksum $calculator): int
    {
        $checksum = $calculator->string($this->name(), 0);

        $checksum = $calculator->char($this->type(), $checksum);

        return $this->getValueChecksumBy($calculator, $checksum);
    }
}
