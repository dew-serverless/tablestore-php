<?php

namespace Dew\Tablestore\Cells;

use DateTimeInterface;

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
}
