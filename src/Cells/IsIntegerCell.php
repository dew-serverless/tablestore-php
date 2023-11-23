<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\CalculatesChecksum;
use Dew\Tablestore\PlainbufferReader;
use Dew\Tablestore\PlainbufferWriter;

trait IsIntegerCell
{
    /**
     * Create an integer cell.
     */
    public function __construct(
        protected string $name,
        protected int $value
    ) {
        //
    }

    /**
     * The value type of the cell.
     */
    public function type(): int
    {
        return ValueType::VT_INTEGER;
    }

    /**
     * The value of the cell.
     */
    public function value(): int
    {
        return $this->value;
    }

    /**
     * The allocated size in byte for the value in buffer.
     *
     * formatted_value = value_type value_len value_data
     * value_type = int8
     * value_len = int32
     */
    public function valueSize(): int
    {
        [$typeSize, $dataSize] = [1, 8];

        return $typeSize + $dataSize;
    }

    /**
     * Get value from the formatted value in buffer.
     */
    public static function fromFormattedValue(PlainbufferReader $buffer): int
    {
        return $buffer->readLittleEndian64();
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
        // value_type: 1 byte
        $buffer->writeChar($this->type());

        // value_data: 8 bytes
        $buffer->writeLittleEndian64($this->value());
    }

    /**
     * Calculate checksum for the cell value.
     */
    public function getValueChecksumBy(CalculatesChecksum $calculator, int $checksum): int
    {
        return $calculator->int64($this->value, $checksum);
    }
}
