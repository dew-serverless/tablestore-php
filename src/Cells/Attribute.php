<?php

namespace Dew\Tablestore\Cells;

use DateTimeInterface;
use Dew\Tablestore\Contracts\Attribute as AttributeContract;
use Dew\Tablestore\Contracts\CalculatesChecksum;

abstract class Attribute extends Cell implements AttributeContract
{
    /**
     * The timestamp of the cell.
     */
    protected ?int $timestamp = null;

    /**
     * The operation applied to the cell.
     */
    protected ?int $operation = null;

    /**
     * Set the timestamp of the cell.
     */
    public function setTimestamp(DateTimeInterface|int $timestamp): self
    {
        // U: seconds since the Unix Epoch
        // v: milliseconds
        $this->timestamp = $timestamp instanceof DateTimeInterface
            ? (int) $timestamp->format('Uv')
            : $timestamp;

        return $this;
    }

    /**
     * Get the timestamp of the cell.
     */
    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    /**
     * Set operation to the cell.
     */
    protected function setOperation(int $type): self
    {
        $this->operation = $type;

        return $this;
    }

    /**
     * Get the operation applied to the cell.
     */
    public function getOperation(): ?int
    {
        return $this->operation;
    }

    /**
     * Calculate checksum for the cell.
     */
    public function getChecksumBy(CalculatesChecksum $calculator): int
    {
        $checksum = parent::getChecksumBy($calculator);

        if ($this->getTimestamp() !== null) {
            $checksum = $calculator->int64($this->getTimestamp(), $checksum);
        }

        if ($this->getOperation() !== null) {
            return $calculator->char($this->getOperation(), $checksum);
        }

        return $checksum;
    }
}
