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
     * Set the timestamp of the cell.
     */
    public function setTimestamp(DateTimeInterface|int $timestamp): self
    {
        $this->timestamp = $timestamp instanceof DateTimeInterface
            ? $timestamp->getTimestamp()
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
     * Calculate checksum for the cell.
     */
    public function getChecksumBy(CalculatesChecksum $calculator): int
    {
        $checksum = parent::getChecksumBy($calculator);

        if ($this->getTimestamp() !== null) {
            return $calculator->int64($this->getTimestamp(), $checksum);
        }

        return $checksum;
    }
}
