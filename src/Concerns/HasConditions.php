<?php

namespace Dew\Tablestore\Concerns;

use Dew\Tablestore\Attribute;
use Dew\Tablestore\Builder;
use Dew\Tablestore\Cells\Cell;
use Dew\Tablestore\Contracts\PrimaryKey;
use Protos\Filter;
use Protos\ReturnType;
use Protos\RowExistenceExpectation;

/**
 * @phpstan-import-type TCondition from \Dew\Tablestore\ConditionFilter
 *
 * @phpstan-type TColumn \Dew\Tablestore\Cells\Cell|callable(\Dew\Tablestore\Builder): void|\Dew\Tablestore\Cells\Cell[]|array{0: string, 1: mixed, 2?: mixed}[]|string
 */
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
     * @var \Dew\Tablestore\Cells\Cell[]
     */
    public array $whereKeys = [];

    /**
     * The filter conditions.
     *
     * @var TCondition[]
     */
    public array $wheres = [];

    /**
     * The filter applied to the query.
     */
    public ?Filter $filter = null;

    /**
     * The maximal value version to retrieve with.
     */
    public int $maxVersions = 1;

    /**
     * The row existence expectation.
     */
    public int $expectation = RowExistenceExpectation::IGNORE;

    /**
     * The returned row of the response.
     */
    public int $returned = ReturnType::RT_PK;

    /**
     * The number of columns to skip with.
     */
    public ?int $offset = null;

    /**
     * The maximal number of columns to retrieve with.
     */
    public ?int $limit = null;

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
     * Filter rows with the given conditions.
     *
     * @param  \Protos\Filter|TColumn  $name
     */
    public function where(Filter|Cell|callable|array|string $name, mixed $operator = null, mixed $value = null): self
    {
        if (is_array($name) && $name !== []) {
            return $name[0] instanceof PrimaryKey ? $this->whereKey($name) : $this->whereColumn($name);
        }

        if (is_callable($name) || is_string($name)) {
            return $this->whereColumn($name, $operator, $value);
        }

        if ($name instanceof Cell) {
            return $name instanceof PrimaryKey ? $this->whereKey($name) : $this->whereColumn($name);
        }

        if ($name instanceof Filter) {
            return $this->whereFilter($name);
        }

        return $this;
    }

    /**
     * Include rows that meet at least one of the given attribute conditions.
     *
     * @param  TColumn  $name
     */
    public function orWhere(Cell|callable|array|string $name, mixed $operator = null, mixed $value = null): self
    {
        return $this->orWhereColumn($name, $operator, $value);
    }

    /**
     * Exclude rows that meet the given attribute conditions.
     *
     * @param  TColumn  $name
     */
    public function whereNot(Cell|callable|array|string $name, mixed $operator = null, mixed $value = null): self
    {
        return $this->whereNotColumn($name, $operator, $value);
    }

    /**
     * Exclude rows that meet one of the given attribute conditions.
     *
     * @param  TColumn  $name
     */
    public function orWhereNot(Cell|callable|array|string $name, mixed $operator = null, mixed $value = null): self
    {
        return $this->orWhereNotColumn($name, $operator, $value);
    }

    /**
     * Filter rows with the given primary keys.
     *
     * @param  \Dew\Tablestore\Cells\Cell[]|\Dew\Tablestore\Cells\Cell  $primaryKeys
     */
    public function whereKey(array|Cell $primaryKeys): self
    {
        $this->whereKeys = is_array($primaryKeys) ? $primaryKeys : [$primaryKeys];

        return $this;
    }

    /**
     * Filter rows with the given criteria.
     */
    public function whereFilter(Filter $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Include rows that only meet the given attribute conditions.
     *
     * @param  TColumn  $name
     */
    public function whereColumn(Cell|callable|array|string $name, mixed $operator = null, mixed $value = null, string $logical = 'and'): self
    {
        if (is_array($name)) {
            return $this->whereMultiple('whereColumn', $name, $logical);
        }

        $where = $this->normalizeWhere($name, $operator, $value);

        return $this->addWhere($where['comparison'], $where['column'], $logical);
    }

    /**
     * Include rows that meet at least one of the given attribute conditions.
     *
     * @param  TColumn  $name
     */
    public function orWhereColumn(Cell|callable|array|string $name, mixed $operator = null, mixed $value = null): self
    {
        return $this->whereColumn($name, $operator, $value, logical: 'or');
    }

    /**
     * Exclude rows that meet the given attribute conditions.
     *
     * @param  TColumn  $name
     */
    public function whereNotColumn(Cell|callable|array|string $name, mixed $operator = null, mixed $value = null, string $logical = 'and'): self
    {
        if (is_array($name)) {
            return $this->whereMultiple('whereNotColumn', $name, $logical);
        }

        $where = $this->normalizeWhere($name, $operator, $value);

        return $this->addWhere($where['comparison'], $where['column'], $logical, negative: true);
    }

    /**
     * Exclude rows that meet one of the given attribute conditions.
     *
     * @param  TColumn  $name
     */
    public function orWhereNotColumn(Cell|callable|array|string $name, mixed $operator = null, mixed $value = null): self
    {
        return $this->whereNotColumn($name, $operator, $value, logical: 'or');
    }

    /**
     * Add condition to where clause.
     *
     * @param  \Dew\Tablestore\Cells\Cell|TCondition[]  $cell
     */
    protected function addWhere(string $comparison, Cell|array $cell, string $logical, bool $negative = false): self
    {
        $this->wheres[] = [
            'comparison' => $comparison,
            'column' => $cell,
            'logical' => $logical,
            'negative' => $negative,
        ];

        return $this;
    }

    /**
     * Normalize where parameters.
     *
     * @param  \Dew\Tablestore\Cells\Cell|callable(\Dew\Tablestore\Builder): void|string  $name
     * @return array{comparison: string, column: \Dew\Tablestore\Cells\Cell|TCondition[]}
     */
    protected function normalizeWhere(Cell|callable|string $name, mixed $operator = null, mixed $value = null): array
    {
        if ($value === null) {
            [$operator, $value] = ['=', $operator];
        }

        if (! is_string($operator)) {
            throw new \InvalidArgumentException('Comparison operator accepts =, !=, <>, >, >=, <, or <=.');
        }

        return ['comparison' => $operator, 'column' => $this->normalizeColumnName($name, $value)];
    }

    /**
     * Normalize the column name.
     *
     * @param  \Dew\Tablestore\Cells\Cell|callable(\Dew\Tablestore\Builder): void|string  $name
     * @return \Dew\Tablestore\Cells\Cell|TCondition[]
     */
    protected function normalizeColumnName(Cell|callable|string $name, mixed $value = null): Cell|array
    {
        if (is_string($name)) {
            return Attribute::createFromValue($name, $value);
        }

        if (is_callable($name)) {
            $name($builder = new Builder);

            return $builder->wheres;
        }

        return $name;
    }

    /**
     * Loop through the columns and pass each to the handler.
     *
     * @param  \Dew\Tablestore\Cells\Cell[]|array{0: string, 1: mixed, 2?: mixed}[]  $columns
     */
    protected function whereMultiple(string $handler, array $columns, string $logical): self
    {
        $callback = [$this, $handler];

        if (! is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf('The handler [%s] does not exists.', $handler));
        }

        foreach ($columns as $column) {
            if ($column instanceof Cell) {
                call_user_func_array($callback, ['name' => $column, 'logical' => $logical]);

                continue;
            }

            call_user_func_array($callback, [
                'name' => $column[0],
                'operator' => $column[1],
                'value' => $column[2] ?? null,
                'logical' => $logical,
            ]);
        }

        return $this;
    }

    /**
     * Limit the maximal value version to retrieve with.
     */
    public function maxVersions(int $max): self
    {
        $this->maxVersions = $max;

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
     * Return part of a row.
     *
     * @param  positive-int  $offset
     * @param  positive-int  $limit
     */
    public function offset(int $offset, int $limit): self
    {
        $this->offset = $offset;
        $this->limit = $limit;

        return $this;
    }
}
