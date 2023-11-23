<?php

namespace Dew\Tablestore\Concerns;

use Protos\ColumnPaginationFilter;
use Protos\ComparatorType;
use Protos\CompositeColumnValueFilter;
use Protos\Filter;
use Protos\FilterType;
use Protos\LogicalOperator;
use Protos\SingleColumnValueFilter;

trait InteractsWithFilter
{
    /**
     * Resolve comparator from the given comparison operator.
     */
    protected function getComparator(string $operator): int
    {
        return match ($operator) {
            '=' => ComparatorType::CT_EQUAL,
            '!=', '<>' => ComparatorType::CT_NOT_EQUAL,
            '>' => ComparatorType::CT_GREATER_THAN,
            '>=' => ComparatorType::CT_GREATER_EQUAL,
            '<' => ComparatorType::CT_LESS_THAN,
            '<=' => ComparatorType::CT_LESS_EQUAL,
            default => throw new \InvalidArgumentException(sprintf(
                'Comparison operator accepts =, !=, <>, >, >=, <, or <=, but [%s] is given.', $operator
            )),
        };
    }

    /**
     * Resolve combinator from the given logical operator.
     */
    protected function getCombinator(string $operator): int
    {
        return match ($operator) {
            'not' => LogicalOperator::LO_NOT,
            'and' => LogicalOperator::LO_AND,
            'or' => LogicalOperator::LO_OR,
            default => throw new \InvalidArgumentException(sprintf(
                'Logical operator accepts "and", "or", or "not", but [%s] is given.', $operator
            )),
        };
    }

    /**
     * Wrap the filter into Protobuf filter message.
     */
    protected function wrapFilter(SingleColumnValueFilter|CompositeColumnValueFilter|ColumnPaginationFilter $filter): Filter
    {
        return (new Filter)
            ->setType($this->getFilterType($filter))
            ->setFilter($filter->serializeToString());
    }

    /**
     * Wrap the filter with logical operator "not".
     */
    protected function wrapNot(Filter $filter): Filter
    {
        $filter = (new CompositeColumnValueFilter)
            ->setCombinator(LogicalOperator::LO_NOT)
            ->setSubFilters([$filter]);

        return $this->wrapFilter($filter);
    }

    /**
     * Resolve filter type from the given filter.
     */
    protected function getFilterType(SingleColumnValueFilter|CompositeColumnValueFilter|ColumnPaginationFilter $filter): int
    {
        return match ($filter::class) {
            SingleColumnValueFilter::class => FilterType::FT_SINGLE_COLUMN_VALUE,
            CompositeColumnValueFilter::class => FilterType::FT_COMPOSITE_COLUMN_VALUE,
            ColumnPaginationFilter::class => FilterType::FT_COLUMN_PAGINATION,
            default => throw new \InvalidArgumentException(sprintf(
                'Unsupported filter type [%s] is given.', $filter::class
            )),
        };
    }
}
