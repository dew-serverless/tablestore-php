<?php

namespace Dew\Tablestore;

use Protos\Filter;

/**
 * @phpstan-type TCondition array{comparison: string, column: \Dew\Tablestore\Cells\Cell, logical: string, negative?: bool}
 */
class FilterBuilder
{
    /**
     * Create a filter builder.
     *
     * @param  TCondition[]  $conditions
     */
    public function __construct(
        protected array $conditions
    ) {
        //
    }

    /**
     * Build the Protobuf filter message.
     */
    public function toFilter(): Filter
    {
        if ($this->size() === 1) {
            return $this->buildItem($this->conditions[0])->toFilter();
        }

        return $this->buildRoot($this->conditions)->toFilter();
    }

    /**
     * Build a root condition group.
     *
     * @param  TCondition[]  $conditions
     */
    protected function buildRoot(array $conditions): ConditionGroup
    {
        $conditions = array_map(
            fn ($conditions): ConditionGroup => $this->buildGroup('and', $conditions),
            $this->withPrecedence($conditions)
        );

        return new ConditionGroup('or', $conditions);
    }

    /**
     * Build a condition group.
     *
     * @param  TCondition[]  $conditions
     */
    protected function buildGroup(string $logical, array $conditions): ConditionGroup
    {
        $conditions = array_map(fn ($condition): ConditionGroup|Condition => $this->buildItem($condition), $conditions);

        return new ConditionGroup($logical, $conditions);
    }

    /**
     * Build item from the condition payload.
     *
     * @param  TCondition  $condition
     */
    protected function buildItem(array $condition): ConditionGroup|Condition
    {
        $negative = $condition['negative'] ?? false;

        $item = is_array($condition['column'])
            ? $this->buildRoot($condition['column'])
            : $this->toConditionInstance($condition);

        return $item->not($negative);
    }

    /**
     * Wrap conditions with precedence structure.
     *
     * <code>
     * // Take an expression as example:
     * attr1 = 'foo' and attr2 = 'bar' or attr3 = 'baz'
     *
     * // It should be parsed to:
     * (attr1 = 'foo' and attr2 = 'bar') or (attr3 = 'baz')
     *
     * // And wrapped into the structure, like:
     * [ [attr1 = 'foo', attr2 = 'bar'], [attr3 = 'baz'] ]
     * │ └─────────── and. ───────────┘  └──── and. ───┘ │
     * └────────────────────── or. ──────────────────────┘
     * </code>
     *
     * @param  TCondition[]  $conditions
     * @return TCondition[][]
     */
    protected function withPrecedence(array $conditions): array
    {
        return $this->split($conditions, 'or');
    }

    /**
     * Split the conditions by the given logical operator.
     *
     * @param  TCondition[]  $conditions
     * @return TCondition[][]
     */
    protected function split(array $conditions, string $logical): array
    {
        $result = [];
        $cursor = 0;

        foreach ($conditions as $condition) {
            if ($condition['logical'] === $logical) {
                $cursor++;
            }

            $result[$cursor][] = $condition;
        }

        return $result;
    }

    /**
     * Make a condition instance.
     *
     * @param  TCondition  $condition
     */
    protected function toConditionInstance(array $condition): Condition
    {
        return new Condition($condition);
    }

    /**
     * The size of the conditions.
     */
    public function size(): int
    {
        return count($this->conditions);
    }

    /**
     * Get the conditions of the filter.
     *
     * @return TCondition[]
     */
    public function conditions(): array
    {
        return $this->conditions;
    }
}
