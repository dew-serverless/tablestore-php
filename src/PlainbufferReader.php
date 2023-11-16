<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Exceptions\PlainbufferReaderException;

class PlainbufferReader
{
    /**
     * The current cursor position.
     */
    protected int $current = 0;

    public function __construct(
        protected string $buffer
    ) {
        //
    }

    /**
     * Read unsigned long.
     */
    public function readLittleEndian32(): int
    {
        $unpacked = unpack('V', $this->read(4));

        if ($unpacked === false) {
            throw new PlainbufferReaderException('Failed to read an unsigned long with little endian byte order.');
        }

        return $unpacked[1];
    }

    /**
     * Read unsigned long long in 64 bits with little endian byte order.
     */
    public function readLittleEndian64(): int
    {
        $low = $this->readLittleEndian32();
        $high = $this->readLittleEndian32();

        return ($high << 32) | $low;
    }

    /**
     * Read a double.
     */
    public function readDouble(): float
    {
        $unpacked = unpack('d', $this->read(8));

        if ($unpacked === false) {
            throw new PlainbufferReaderException('Failed to read a double.');
        }

        return $unpacked[1];
    }

    /**
     * Read a char.
     */
    public function readChar(): int
    {
        return ord($this->read(1));
    }

    /**
     * Read the buffer with given length.
     */
    public function read(int $length): string
    {
        $current = $this->current;

        $this->advance($length);

        return substr($this->buffer, $current, $length);
    }

    /**
     * The size of the buffer.
     */
    public function size(): int
    {
        return strlen($this->buffer);
    }

    /**
     * Advance the cursur position.
     */
    protected function advance(int $length): void
    {
        $this->current += $length;
    }

    /**
     * The raw Plainbuffer.
     */
    public function getBuffer(): string
    {
        return $this->buffer;
    }
}
