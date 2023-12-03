<?php

namespace Dew\Tablestore\Concerns;

trait HandleNegation
{
    /**
     * Determine if the one is the opposite of the statement.
     */
    protected bool $negative = false;

    /**
     * Specify the opposite of the statement.
     */
    public function not(bool $negative = true): self
    {
        $this->negative = $negative;

        return $this;
    }

    /**
     * Determine if the one is the opposite of the statement.
     */
    public function isNegative(): bool
    {
        return $this->negative;
    }
}
