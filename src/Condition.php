<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Cells\Cell;
use Dew\Tablestore\Concerns\HandleNegation;
use Dew\Tablestore\Concerns\InteractsWithFilter;
use Dew\Tablestore\Contracts\HasValue;
use Protos\Filter;
use Protos\SingleColumnValueFilter;

/**
 * @phpstan-type TCondition array{comparison: string, column: \Dew\Tablestore\Cells\Cell}
 */
class Condition
{
    use HandleNegation, InteractsWithFilter;

    /**
     * Create a condition.
     *
     * @param  TCondition  $condition
     */
    public function __construct(
        public array $condition
    ) {
        //
    }

    /**
     * The comparison operator of the condition statement.
     */
    public function comparisonOperator(): string
    {
        return $this->condition['comparison'];
    }

    /**
     * The column to compare against.
     */
    public function column(): Cell
    {
        return $this->condition['column'];
    }

    /**
     * Get the formatted cell value in Plainbuffer representation.
     */
    public function getCellValue(Cell $cell): string
    {
        if (! $cell instanceof HasValue) {
            throw new \InvalidArgumentException(sprintf(
                'The column [%s] does not contain value.', $cell->name()
            ));
        }

        $cell->toFormattedValue($buffer = new PlainbufferWriter);

        return $buffer->getBuffer();
    }

    /**
     * Represent the condition in Protobuf filter message.
     */
    public function toFilter(): Filter
    {
        $filter = (new SingleColumnValueFilter)
            ->setComparator($this->getComparator($this->comparisonOperator()))
            ->setColumnName($this->column()->name())
            ->setColumnValue($this->getCellValue($this->column()))
            ->setFilterIfMissing(true)
            ->setLatestVersionOnly(true);

        $filter = $this->wrapFilter($filter);

        return $this->isNegative() ? $this->wrapNot($filter) : $filter;
    }
}
