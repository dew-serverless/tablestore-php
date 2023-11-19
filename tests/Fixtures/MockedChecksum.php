<?php

namespace Dew\Tablestore\Tests\Fixtures;

use Dew\Tablestore\Contracts\CalculatesChecksum;

class MockedChecksum implements CalculatesChecksum
{
    public function string(string $value, int $checksum): int
    {
        return 0;
    }

    public function char(int $value, int $checksum): int
    {
        return 0;
    }

    public function int32(int $value, int $checksum): int
    {
        return 0;
    }

    public function int64(int $value, int $checksum): int
    {
        return 0;
    }

    public function double(float $value, int $checksum): int
    {
        return 0;
    }
}
