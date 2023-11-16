<?php

namespace Dew\Tablestore;

class PlainbufferWriter
{
    /**
     * The buffer.
     */
    protected string $buffer = '';

    /**
     * Write unsigned long in 32 bits with little endian byte order.
     */
    public function writeLittleEndian32(int $value): self
    {
        return $this->write(pack('V', $value));
    }

    /**
     * Write unsigned long long in 64 bits with little endian byte order.
     */
    public function writeLittleEndian64(int $value): self
    {
        return $this->writeLittleEndian32($value & 0xFFFFFFFF)
            ->writeLittleEndian32(($value >> 32) & 0xFFFFFFFF);
    }

    /**
     * Write a double to buffer.
     */
    public function writeDouble(float $value): self
    {
        return $this->write(pack('d', $value));
    }

    /**
     * Write a char to buffer.
     */
    public function writeChar(int $codepoint): self
    {
        return $this->write(chr($codepoint));
    }

    /**
     * Write the value to buffer.
     */
    public function write(string $value): self
    {
        $this->buffer .= $value;

        return $this;
    }

    /**
     * The size of the buffer.
     */
    public function size(): int
    {
        return strlen($this->buffer);
    }

    /**
     * Get the buffer.
     */
    public function getBuffer(): string
    {
        return $this->buffer;
    }
}
