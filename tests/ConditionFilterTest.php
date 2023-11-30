<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\ConditionFilter;

test('single condition', function () {
    // expression: attr1 = 'foo'
    $builder = new ConditionFilter([['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and']]);
    expect($builder->toFilter())->toBeSingleValueFilter();
});

test('single condition not', function () {
    // expression: not attr1 = 'foo'
    $builder = new ConditionFilter([['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and', 'negative' => true]]);
    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});

test('single condition group', function () {
    // expression: (attr1 = 'foo')
    $builder = new ConditionFilter([[
        'comparison' => '=',
        'column' => [['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and']],
        'logical' => 'and',
    ]]);
    expect($builder->toFilter())->toBeSingleValueFilter();
});

test('single condition negation group', function () {
    // expression: not (attr1 = 'foo')
    $builder = new ConditionFilter([[
        'comparison' => '=',
        'column' => [['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and']],
        'logical' => 'and',
        'negative' => true,
    ]]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});

test('multiple conditions and', function () {
    // expression: attr1 = 'foo' and attr2 = 'bar'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and'],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: attr1 = 'foo' and attr2 = 'bar'
        //           ~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~
        //           sub-filter 0      sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('multiple conditions or', function () {
    // expression: attr1 = 'foo' or attr2 = 'bar'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'or'],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: attr1 = 'foo' or attr2 = 'bar'
        //           ~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~
        //           sub-filter 0     sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('multiple conditions or or', function () {
    // expression: attr1 = 'foo' or attr2 = 'bar'

    // The test case is the same as the one above, and we intentionally add
    // it cause it covers the scenario when user building a query filter
    // with the method "orWhere" chaining to right from the beginning.
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'or'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'or'],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: attr1 = 'foo' or attr2 = 'bar'
        //           ~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~
        //           sub-filter 0     sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('multiple conditions and-not', function () {
    // expression: not attr1 = 'foo' and not attr2 = 'bar'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and', 'negative' => true],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and', 'negative' => true],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: not attr1 = 'foo' and not attr2 = 'bar'
        //           ~~~~~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~~~~~
        //           sub-filter 0          sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeCompositeValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter();
});

test('multiple conditions or-not', function () {
    // expression: not attr1 = 'foo' or not attr2 = 'bar'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and', 'negative' => true],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'or', 'negative' => true],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: not attr1 = 'foo' or not attr2 = 'bar'
        //           ~~~~~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~~~
        //           sub-filter 0         sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeCompositeValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter();
});

test('operator precedence and', function () {
    // expression: attr1 = 'foo' and attr2 = 'bar' and attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'and'],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: (attr1 = 'foo') and (attr2 = 'bar') and (attr3 = 'baz')
        //           ~~~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~~~
        //           sub-filter 0        sub-filter 1        sub-filter 2
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(3)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[2])->toBeSingleValueFilter();
});

test('operator precedence and or', function () {
    // expression: attr1 = 'foo' and attr2 = 'bar' or attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'or'],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: (attr1 = 'foo' and attr2 = 'bar') or attr3 = 'baz'
        //           ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~
        //           sub-filter 0                         sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeCompositeValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[0]))

        // expected: attr1 = 'foo' and attr2 = 'bar'
        //           ~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~
        //           sub-filter 0      sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('operator precedence and and-not', function () {
    // expression: attr1 = 'foo' and attr2 = 'bar' and not attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'and', 'negative' => true],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: (attr1 = 'foo') and (attr2 = 'bar') and (not attr3 = 'baz')
        //           ~~~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0        sub-filter 1        sub-filter 2
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(3)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[2])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[2]))

        // expected: not attr3 = 'baz'
        //           ^^^ ~~~~~~~~~~~~~
        //               sub-filter 0
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});

test('operator precedence and or-not', function () {
    // expression: attr1 = 'foo' and attr2 = 'bar' or not attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'or', 'negative' => true],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: (attr1 = 'foo' and attr2 = 'bar') or (not attr3 = 'baz')
        //           ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0                         sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeCompositeValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($left = unwrapFilter($filter->getSubFilters()[0]))
        ->and($right = unwrapFilter($filter->getSubFilters()[1]))

        // expected: attr1 = 'foo' and attr2 = 'bar'
        //           ~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~
        //           sub-filter 0      sub-filter 1
        ->and($left->getCombinator())->toBeLogicalAnd()
        ->and($left->getSubFilters())->toHaveCount(2)
        ->and($left->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($left->getSubFilters()[1])->toBeSingleValueFilter()

        // expected: not attr3 = 'baz'
        //           ^^^ ~~~~~~~~~~~~~
        //               sub-filter 0
        ->and($right->getCombinator())->toBeLogicalNot()
        ->and($right->getSubFilters())->toHaveCount(1)
        ->and($right->getSubFilters()[0])->toBeSingleValueFilter();
});

test('operator precedence or', function () {
    // expression: attr1 = 'foo' or attr2 = 'bar' or attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'or'],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'or'],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: (attr1 = 'foo') or (attr2 = 'bar') or (attr3 = 'baz')
        //           ~~~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~
        //           sub-filter 0       sub-filter 1       sub-filter 2
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(3)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[2])->toBeSingleValueFilter();
});

test('operator precedence or and', function () {
    // expression: attr1 = 'foo' or attr2 = 'bar' and attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'or'],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'and'],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: attr1 = 'foo' or (attr2 = 'bar' and attr3 = 'baz')
        //           ~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0     sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // assert: attr2 = 'bar' and attr3 = 'baz'
        //         ~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~
        //         sub-filter 0     sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('operator precedence or and-not', function () {
    // expression: attr1 = 'foo' or attr2 = 'bar' and not attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'or'],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'and', 'negative' => true],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: attr1 = 'foo' or (attr2 = 'bar' and not attr3 = 'baz')
        //           ~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0     sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // expected: attr2 = 'bar' and (not attr3 = 'baz')
        //           ~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0      sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // expected: not attr3 = 'baz'
        //           ^^^ ~~~~~~~~~~~~~
        //               sub-filter 0
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});

test('operator precedence or or-not', function () {
    // expression: attr1 = 'foo' or attr2 = 'bar' or not attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'or'],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'or', 'negative' => true],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: (attr1 = 'foo') or (attr2 = 'bar') or (not attr3 = 'baz')
        //           ~~~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0       sub-filter 1       sub-filter 2
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(3)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[2])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[2]))

        // expected: not attr3 = 'baz'
        //           ^^^ ~~~~~~~~~~~~~
        //               sub-filter 0
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});

test('operator precedence and-not and', function () {
    // expression: attr1 = 'foo' and not attr2 = 'bar' and attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and', 'negative' => true],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'and'],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: (attr1 = 'foo') and (not attr2 = 'bar') and (attr3 = 'baz')
        //           ~~~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~~~
        //           sub-filter 0        sub-filter 1            sub-filter 2
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(3)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter->getSubFilters()[2])->toBeSingleValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // expected: not attr2 = 'bar'
        //           ^^^ ~~~~~~~~~~~~~
        //               sub-filter 0
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});

test('operator precedence and-not or', function () {
    // expression: attr1 = 'foo' and not attr2 = 'bar' or attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and', 'negative' => true],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'or'],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: (attr1 = 'foo' and not attr2 = 'bar') or attr3 = 'baz'
        //           ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~
        //           sub-filter 0                             sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeCompositeValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[0]))

        // expected: attr1 = 'foo' and (not attr2 = 'bar')
        //           ~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0      sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // expected: not attr2 = 'bar'
        //           ^^^ ~~~~~~~~~~~~~
        //               sub-filter 0
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});

test('operator precedence or-not and', function () {
    // expression: attr1 = 'foo' or not attr2 = 'bar' and attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'or', 'negative' => true],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'and'],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: attr1 = 'foo' or (not attr2 = 'bar' and attr3 = 'baz')
        //           ~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0     sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // expected: (not attr2 = 'bar') and attr3 = 'baz'
        //           ~~~~~~~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~
        //           sub-filter 0            sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeCompositeValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[0]))

        // expected: not attr2 = 'bar'
        //           ^^^ ~~~~~~~~~~~~~
        //               sub-filter 0
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});

test('operator precedence or-not or', function () {
    // expression: attr1 = 'foo' or not attr2 = 'bar' or attr3 = 'baz'
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'or', 'negative' => true],
        ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'or'],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: (attr1 = 'foo') or (not attr2 = 'bar') or (attr3 = 'baz')
        //           ~~~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~
        //           sub-filter 0       sub-filter 1           sub-filter 2
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(3)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter->getSubFilters()[2])->toBeSingleValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // expected: not attr2 = 'bar'
        //           ^^^ ~~~~~~~~~~~~~
        //               sub-filter 0
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});

test('grouping with and', function () {
    // expression: attr1 = 'foo' and (attr2 = 'bar' or attr3 = 'baz')
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        [
            'comparison' => '=',
            'column' => [
                ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and'],
                ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'or'],
            ],
            'logical' => 'and',
        ],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: attr1 = 'foo' and (attr2 = 'bar' or attr3 = 'baz')
        //           ~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0      sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // expected: attr2 = 'bar' or attr3 = 'baz'
        //           ~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~
        //           sub-filter 0     sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('grouping with or', function () {
    // expression: attr1 = 'foo' or (attr2 = 'bar' or attr3 = 'baz')
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        [
            'comparison' => '=',
            'column' => [
                ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and'],
                ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'or'],
            ],
            'logical' => 'or',
        ],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: attr1 = 'foo' or (attr2 = 'bar' or attr3 = 'baz')
        //           ~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0      sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // expected: attr2 = 'bar' or attr3 = 'baz'
        //           ~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~
        //           sub-filter 0     sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('grouping with and not', function () {
    // expression: attr1 = 'foo' and not (attr2 = 'bar' or attr3 = 'baz')
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        [
            'comparison' => '=',
            'column' => [
                ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and'],
                ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'or'],
            ],
            'logical' => 'and',
            'negative' => true,
        ],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: attr1 = 'foo' and (not (attr2 = 'bar' or attr3 = 'baz'))
        //           ~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0      sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // expected: not (attr2 = 'bar' or attr3 = 'baz')
        //           ^^^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //               sub-filter 0
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[0]))

        // expected: attr2 = 'bar' or attr3 = 'baz'
        //           ~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~
        //           sub-filter 0     sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});

test('grouping with or not', function () {
    // expression: attr1 = 'foo' or not (attr2 = 'bar' and attr3 = 'baz')
    $builder = new ConditionFilter([
        ['comparison' => '=', 'column' => Attribute::string('attr1', 'foo'), 'logical' => 'and'],
        [
            'comparison' => '=',
            'column' => [
                ['comparison' => '=', 'column' => Attribute::string('attr2', 'bar'), 'logical' => 'and'],
                ['comparison' => '=', 'column' => Attribute::string('attr3', 'baz'), 'logical' => 'and'],
            ],
            'logical' => 'or',
            'negative' => true,
        ],
    ]);

    expect($filter = $builder->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: attr1 = 'foo' or (not (attr2 = 'bar' and attr3 = 'baz'))
        //           ~~~~~~~~~~~~~ ^^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //           sub-filter 0     sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // expected: not (attr2 = 'bar' and attr3 = 'baz')
        //           ^^^ ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //               sub-filter 0
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[0]))

        // expected: attr2 = 'bar' and attr3 = 'baz'
        //           ~~~~~~~~~~~~~ ^^^ ~~~~~~~~~~~~~
        //           sub-filter 0      sub-filter 1
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter();
});
