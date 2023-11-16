<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\CalculatesChecksum;
use Dew\Tablestore\PlainbufferReader;
use Dew\Tablestore\PlainbufferWriter;

trait IsBooleanCell
{
    /**
     * Create a boolean cell.
     */
    public function __construct(
        protected string $name,
        protected bool $value
    ) {
        //
    }

    /**
     * The value type of the cell.
     */
    public function type(): int
    {
        return ValueType::VT_BOOLEAN;
    }

    /**
     * The value of the cell.
     */
    public function value(): bool
    {
        return $this->value;
    }

    /**
     * Get value from the formatted value in buffer.
     */
    public static function fromFormattedValue(PlainbufferReader $buffer): bool
    {
        return (bool) $buffer->readChar();
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
        [$typeSize, $dataSize] = [1, 1];

        $buffer->writeLittleEndian32($typeSize + $dataSize);

        // value_type: 1 byte
        $buffer->writeChar($this->type());

        // value_data: 1 byte
        $buffer->writeChar((int) $this->value());
    }

    /**
     * Calculate checksum for the cell value.
     */
    public function getValueChecksumBy(CalculatesChecksum $calculator, int $checksum): int
    {
        return $calculator->char((int) $this->value(), $checksum);
    }
}
