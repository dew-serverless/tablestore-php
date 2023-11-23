<?php

namespace Dew\Tablestore\Contracts;

use Dew\Tablestore\PlainbufferReader;
use Dew\Tablestore\PlainbufferWriter;

interface HasValue
{
    /**
     * The value.
     */
    public function value(): mixed;

    /**
     * The value type.
     */
    public function type(): int;

    /**
     * The allocated size in byte for the value in buffer.
     *
     * formatted_value = value_type value_len value_data
     * value_type = int8
     * value_len = int32
     */
    public function valueSize(): int;

    /**
     * Get value from the formatted value in buffer.
     */
    public static function fromFormattedValue(PlainbufferReader $buffer): mixed;

    /**
     * Build formatted value to buffer.
     *
     * formatted_value = value_type value_len value_data
     * value_type = int8
     * value_len = int32
     */
    public function toFormattedValue(PlainbufferWriter $buffer): void;

    /**
     * Calculate checksum for the value.
     */
    public function getValueChecksumBy(CalculatesChecksum $calculator, int $checksum): int;
}
