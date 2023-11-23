<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\Condition;
use Dew\Tablestore\ConditionGroup;

test('group size', function () {
    $group = new ConditionGroup('and', [
        new Condition(['comparison' => '=', 'column' => Attribute::string('value', 'foo'), 'logical' => 'and']),
        new ConditionGroup('and', []),
    ]);

    expect($group->size())->toBe(2);
});

test('filter generation and', function () {
    $group = new ConditionGroup('and', [
        new Condition(['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and']),
        new Condition(['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and']),
    ]);

    expect($filter = $group->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('filter generation or', function () {
    $group = new ConditionGroup('or', [
        new Condition(['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'or']),
        new Condition(['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'or']),
    ]);

    expect($filter = $group->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('filter generation nested', function () {
    // expression: (attr1 = 'foo' or attr2 = 'bar') and attr3 = 'baz'
    $group = new ConditionGroup('and', [
        new ConditionGroup('or', [
            new Condition(['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'or']),
            new Condition(['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'or']),
        ]),
        new Condition(['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'and']),
    ]);

    expect($filter = $group->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: (attr1 = 'foo' or attr2 = 'bar') and attr3 = 'baz'
        //           ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~
        //           sub-filter 0                         sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeCompositeValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[0]))

        // expected: attr1 = 'foo' or attr2 = 'bar'
        //           ~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~
        //           sub-filter 0     sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('filter generation negation', function () {
    // expression: not (attr1 = 'foo' and attr2 = 'bar')
    $group = (new ConditionGroup('and', [
        new Condition(['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and']),
        new Condition(['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and']),
    ]))->not();

    expect($filter = $group->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: not (attr1 = 'foo' and attr2 = 'bar')
        //           ^^^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //               sub-filter 0
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[0]))

        // expected: attr1 = 'foo' and attr2 = 'bar'
        //           ~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~
        //           sub-filter 0      sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('could not build with an empty group', function () {
    $group = new ConditionGroup('and', []);
    expect(fn () => $group->toFilter())
        ->toThrow(InvalidArgumentException::class, 'Could not build a filter with an empty group.');
});

test('skip the wrapper when it has only one condition', function () {
    $group = new ConditionGroup('and', [
        new Condition(['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and']),
    ]);
    expect($group->toFilter())->toBeSingleValueFilter();
});

test('should not skip the wrapper when the group is a negation', function () {
    $group = (new ConditionGroup('and', [
        new Condition(['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and']),
    ]))->not();

    expect($filter = $group->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});

test('logical operator accepts only and or or', function () {
    expect(fn () => new ConditionGroup('not', []))
        ->toThrow(InvalidArgumentException::class, 'The condition group accepts only logical operator "and" or "or".');
});
