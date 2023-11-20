<?php

namespace Dew\Tablestore\Cells;

use DateTimeInterface;
use Dew\Tablestore\Contracts\CalculatesChecksum;
use Dew\Tablestore\PlainbufferReader;
use Dew\Tablestore\PlainbufferWriter;
use LogicException;

class DeleteAttribute extends Attribute
{
    /**
     * The operation applied to the cell.
     */
    protected ?int $operation = Operation::DELETE_ALL_VERSIONS;

    /**
     * Create an awaiting deletion attribute.
     */
    public function __construct(
        protected string $name
    ) {
        //
    }

    /**
     * Delete all the versions.
     */
    public function all(): self
    {
        $this->operation = Operation::DELETE_ALL_VERSIONS;
        $this->timestamp = null;

        return $this;
    }

    /**
     * Delete the given version.
     */
    public function version(DateTimeInterface|int $timestamp): self
    {
        $this->operation = Operation::DELETE_ONE_VERSION;
        $this->setTimestamp($timestamp);

        return $this;
    }

    /**
     * The value of the cell.
     */
    public function value(): mixed
    {
        return null;
    }

    /**
     * The value type of the cell.
     */
    public function type(): int
    {
        throw new LogicException('Awaiting deletion attribute does not contain a real value.');
    }

    /**
     * Get value from the formatted value in buffer.
     */
    public static function fromFormattedValue(PlainbufferReader $buffer): mixed
    {
        throw new LogicException('Awaiting deletion attribute does not contain a real value.');
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
        throw new LogicException('Awaiting deletion attribute does not contain a real value.');
    }

    /**
     * Calculate checksum for the cell value.
     */
    public function getValueChecksumBy(CalculatesChecksum $calculator, int $checksum): int
    {
        throw new LogicException('Awaiting deletion attribute does not contain a real value.');
    }
}
