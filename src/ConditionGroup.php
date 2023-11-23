<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Concerns\HandleNegation;
use Dew\Tablestore\Concerns\InteractsWithFilter;
use Protos\CompositeColumnValueFilter;
use Protos\Filter;

/**
 * @phpstan-type TItem \Dew\Tablestore\ConditionGroup|\Dew\Tablestore\Condition
 */
class ConditionGroup
{
    use HandleNegation, InteractsWithFilter;

    /**
     * The supported logical operators.
     */
    public const SUPPORTED = [
        'and',
        'or',
    ];

    /**
     * Create a condition group.
     *
     * @param  TItem[]  $items
     */
    public function __construct(
        protected string $logicalOperator,
        protected array $items
    ) {
        if (! in_array($this->logicalOperator, self::SUPPORTED, strict: true)) {
            throw new \InvalidArgumentException('The condition group accepts only logical operator "and" or "or".');
        }
    }

    /**
     * Represent the condition group in Protobuf filter message.
     */
    public function toFilter(): Filter
    {
        if ($this->size() === 0) {
            throw new \InvalidArgumentException('Could not build a filter with an empty group.');
        }

        $filter = $this->buildFilter();

        return $this->isNegative() ? $this->wrapNot($filter) : $filter;
    }

    /**
     * Build Protobuf filter message.
     */
    protected function buildFilter(): Filter
    {
        if ($this->size() === 1) {
            return $this->items[0]->toFilter();
        }

        return $this->wrapFilter((new CompositeColumnValueFilter)
            ->setCombinator($this->getCombinator($this->logicalOperator()))
            ->setSubFilters(array_map(fn ($item): Filter => $item->toFilter(), $this->items)));
    }

    /**
     * Get the logical operator.
     */
    public function logicalOperator(): string
    {
        return $this->logicalOperator;
    }

    /**
     * The size of the group.
     */
    public function size(): int
    {
        return count($this->items);
    }

    /**
     * Get the items of the group.
     *
     * @return TItem[]
     */
    public function items(): array
    {
        return $this->items;
    }
}
