<?php

namespace Dew\Tablestore\Concerns;

trait FilterRows
{
    /**
     * A list of column names to retrieve with.
     *
     * @var string[]
     */
    public array $selects = [];

    /**
     * The primary keys to filter the rows.
     *
     * @var (\Dew\Tablestore\Cells\Cell&\Dew\Tablestore\Contracts\PrimaryKey)[]
     */
    public array $wheres = [];

    /**
     * The maximal value version to retrieve with.
     */
    public int $takes = 1;

    /**
     * Select columns to retrieve with.
     *
     * @param  string[]  $cells
     */
    public function select(array $cells): self
    {
        $this->selects = $cells;

        return $this;
    }

    /**
     * Filter rows by the given primary keys.
     *
     * @param  (\Dew\Tablestore\Cells\Cell&\Dew\Tablestore\Contracts\PrimaryKey)[]  $primaryKeys
     */
    public function where(array $primaryKeys): self
    {
        $this->wheres = $primaryKeys;

        return $this;
    }

    /**
     * Limit the maximal value version to retrieve with.
     */
    public function take(int $versions): self
    {
        $this->takes = $versions;

        return $this;
    }
}
