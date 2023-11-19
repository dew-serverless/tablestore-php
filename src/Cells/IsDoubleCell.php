<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\CalculatesChecksum;
use Dew\Tablestore\PlainbufferReader;
use Dew\Tablestore\PlainbufferWriter;

trait IsDoubleCell
{
    /**
     * Create a double cell.
     */
    public function __construct(
        protected string $name,
        protected float $value
    ) {
        //
    }

    /**
     * The value type of the cell.
     */
    public function type(): int
    {
        return ValueType::VT_DOUBLE;
    }

    /**
     * The value of the cell.
     */
    public function value(): float
    {
        return $this->value;
    }

    /**
     * Get value from the formatted value in buffer.
     */
    public static function fromFormattedValue(PlainbufferReader $buffer): float
    {
        return $buffer->readDouble();
    }

    /**
     * Build formatted value to buffer.
     *
     * formatted_value = value_type value_len value_data
     * value_type = int8
     * value_len = int32
     */
    public function toFormattedValue(PlainbufferWriter $buffer): void
    {
        [$typeSize, $dataSize] = [1, 8];

        $buffer->writeLittleEndian32($typeSize + $dataSize);

        // value_type: 1 byte
        $buffer->writeChar($this->type());

        // value_data: 8 bytes
        $buffer->writeDouble($this->value());
    }

    /**
     * Calculate checksum for the cell value.
     */
    public function getValueChecksumBy(CalculatesChecksum $calculator, int $checksum): int
    {
        return $calculator->double($this->value(), $checksum);
    }
}
