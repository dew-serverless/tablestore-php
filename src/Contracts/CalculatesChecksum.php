<?php

namespace Dew\Tablestore\Contracts;

interface CalculatesChecksum
{
    /**
     * Calculate checksum with the given string.
     */
    public function string(string $value, int $checksum): int;

    /**
     * Calculate checksum with the given char.
     */
    public function char(int $value, int $checksum): int;

    /**
     * Calculate checksum with the given integer value in 4 bytes.
     */
    public function int32(int $value, int $checksum): int;

    /**
     * Calculate checksum with the given integer value in 8 bytes.
     */
    public function int64(int $value, int $checksum): int;

    /**
     * Calculate checksum with the given double value.
     */
    public function double(float $double, int $checksum): int;
}
