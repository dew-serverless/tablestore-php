<?php

namespace Dew\Tablestore\Concerns;

use Protos\Condition;
use Protos\ReturnContent;
use Protos\ReturnType;
use Protos\RowExistenceExpectation;

trait HasConditions
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
     * The row existence expectation.
     */
    public int $expectation = RowExistenceExpectation::IGNORE;

    /**
     * The returned row of the response.
     */
    public int $returned = ReturnType::RT_PK;

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

    /**
     * Expect the row is existing.
     */
    public function expectExists(): self
    {
        $this->expectation = RowExistenceExpectation::EXPECT_EXIST;

        return $this;
    }

    /**
     * Expect the row is missing.
     */
    public function expectMissing(): self
    {
        $this->expectation = RowExistenceExpectation::EXPECT_NOT_EXIST;

        return $this;
    }

    /**
     * Ignore whether the row is existing or not.
     */
    public function ignoreExistence(): self
    {
        $this->expectation = RowExistenceExpectation::IGNORE;

        return $this;
    }

    /**
     * Return response without rows.
     */
    public function withoutReturn(): self
    {
        $this->returned = ReturnType::RT_NONE;

        return $this;
    }

    /**
     * Return the primary keys in response.
     */
    public function returnPrimaryKey(): self
    {
        $this->returned = ReturnType::RT_PK;

        return $this;
    }

    /**
     * Return the modified attributes in response.
     */
    public function returnModified(): self
    {
        $this->returned = ReturnType::RT_AFTER_MODIFY;

        return $this;
    }

    /**
     * Build a condition Protobuf message.
     */
    protected function toCondition(): Condition
    {
        $condition = new Condition;
        $condition->setRowExistence($this->expectation);

        return $condition;
    }

    /**
     * Build a return content Protobuf message.
     */
    protected function toReturnContent(): ReturnContent
    {
        $content = new ReturnContent;
        $content->setReturnType($this->returned);
        $content->setReturnColumnNames($this->selects);

        return $content;
    }
}
