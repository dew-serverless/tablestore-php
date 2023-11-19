<?php

namespace Dew\Tablestore;

class HexDumper
{
    /**
     * The codepoint indicates a space character.
     */
    protected const FIRST_PRINTABLE_ASCII_CHARACTER_CODEPOINT = 32;

    /**
     * The codepoint indicates a ~ character.
     */
    protected const LAST_PRINTABLE_ASCII_CHARACTER_CODEPOINT = 126;

    /**
     * The size of the ASCII table.
     */
    protected const ASCII_TABLE_SIZE = 256;

    /**
     * The ASCII table with original character and printable character pairs.
     *
     * @var array<string, string>
     */
    protected static array $replacementPairs = [];

    /**
     * The number of bytes to dipslay per line.
     *
     * @var positive-int
     */
    public int $width = 16;

    /**
     * Placeholder for non-visible character in display.
     */
    public string $nonVisible = '.';

    /**
     * Create a hex dumper.
     */
    public function __construct(
        protected string $buffer
    ) {
        //
    }

    /**
     * Set the number of bytes to dipslay per line.
     *
     * @param  positive-int  $width
     */
    public function width(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Set non-visible character placeholder.
     */
    public function nonVisibleUsing(string $character): self
    {
        $this->nonVisible = $character;

        return $this;
    }

    /**
     * Get the buffer in hexadecimal representation.
     */
    public function toHex(): string
    {
        return bin2hex($this->buffer);
    }

    /**
     * Dump the buffer.
     */
    public function dump(): void
    {
        $lines = str_split($this->toHex(), $this->width * 2);
        $display = str_split($this->toHumanReadableDisplay(), $this->width);

        $offset = 0;

        foreach ($lines as $i => $line) {
            // Split the line into 2-digit chunks for readability.
            $hex = implode(' ', str_split($line, 2));

            printf("%6X : %-{$this->reservedForHex()}s[%s]\n",
                $offset, $hex, $display[$i]
            );

            $offset += $this->width;
        }
    }

    /**
     * The reserved space for displaying hex column.
     */
    protected function reservedForHex(): int
    {
        [$hex, $space] = [2, 1];

        // 00 00 00 00 00 <- ending with a space
        // ~~~^^^~~~^^^~~~
        // We need 3 spaces for displaying one hex value.
        return $this->width * ($hex + $space);
    }

    /**
     * Build a human readable display for buffer.
     */
    protected function toHumanReadableDisplay(): string
    {
        return strtr($this->buffer, $this->replacementPairs());
    }

    /**
     * The ASCII table with original character and printable character pairs.
     *
     * @return array<string, string>
     */
    protected function replacementPairs(): array
    {
        if (static::$replacementPairs !== []) {
            return static::$replacementPairs;
        }

        $pairs = [];

        for ($i = 0; $i < self::ASCII_TABLE_SIZE; $i++) {
            $char = chr($i);

            $pairs[$char] = $this->isPrintableCharacter($char) ? $char : $this->nonVisible;
        }

        return static::$replacementPairs = $pairs;
    }

    /**
     * Determine if the character is printable.
     */
    protected function isPrintableCharacter(string $char): bool
    {
        $codepoint = ord($char);

        return $codepoint >= self::FIRST_PRINTABLE_ASCII_CHARACTER_CODEPOINT
            && $codepoint <= self::LAST_PRINTABLE_ASCII_CHARACTER_CODEPOINT;
    }
}
