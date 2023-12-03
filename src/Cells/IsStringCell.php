<?php

namespace Dew\Tablestore\Cells;

use Dew\Tablestore\Contracts\CalculatesChecksum;
use Dew\Tablestore\PlainbufferReader;
use Dew\Tablestore\PlainbufferWriter;

trait IsStringCell
{
    /**
     * Create a string cell.
     */
    public function __construct(
        protected string $name,
        protected string $value
    ) {
        //
    }

    /**
     * The value type of the cell.
     */
    public function type(): int
    {
        return ValueType::VT_STRING;
    }

    /**
     * The value of the cell.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * The allocated size of the value in buffer.
     *
     * formatted_value = value_type value_len value_data
     * value_type = int8
     * value_len = int32
     */
    public function valueSize(): int
    {
        [$typeSize, $lenSize, $dataSize] = [1, 4, strlen($this->value)];

        return $typeSize + $lenSize + $dataSize;
    }

    /**
     * Get value from the formatted value in buffer.
     */
    public static function fromFormattedValue(PlainbufferReader $buffer): string
    {
        return $buffer->read(              // 2: read value by the size
            $buffer->readLittleEndian32()  // 1: read the value size
        );
    }

    /**
     * The allocated size in byte for the value in buffer.
     *
     * formatted_value = value_type value_len value_data
     * value_type = int8
     * value_len = int32
     */
    public function toFormattedValue(PlainbufferWriter $buffer): void
    {
        // value_type: 1 byte
        $buffer->writeChar($this->type());

        // value_len: 4 bytes
        $buffer->writeLittleEndian32(strlen($this->value));

        // value_data
        $buffer->write($this->value);
    }

    /**
     * Calculate checksum for the cell value.
     */
    public function getValueChecksumBy(CalculatesChecksum $calculator, int $checksum): int
    {
        $checksum = $calculator->int32(strlen($this->value), $checksum);

        return $calculator->string($this->value, $checksum);
    }
}
