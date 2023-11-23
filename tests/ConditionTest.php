<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\Condition;
use Dew\Tablestore\PlainbufferWriter;
use Protos\ComparatorType;

test('filter generation', function (string $operator, int $comparator) {
    $attribute = Attribute::integer('value', 100);
    $attribute->toFormattedValue($buffer = new PlainbufferWriter);
    $condition = new Condition(['comparison' => $operator, 'column' => $attribute, 'logical' => 'and']);
    expect($filter = $condition->toFilter())->toBeSingleValueFilter()
        ->and($filter = unwrapFilter($filter))
        ->and($filter->getComparator())->toBe($comparator)
        ->and($filter->getColumnName())->toBe($attribute->name())
        ->and($filter->getColumnValue())->toBe($buffer->getBuffer())
        ->and($filter->getFilterIfMissing())->toBeTrue()
        ->and($filter->getLatestVersionOnly())->toBeTrue();
})->with([
    'equals operator' => ['=', ComparatorType::CT_EQUAL],
    'not equals operator !=' => ['!=', ComparatorType::CT_NOT_EQUAL],
    'not equals operator <>' => ['<>', ComparatorType::CT_NOT_EQUAL],
    'greater than operator' => ['>', ComparatorType::CT_GREATER_THAN],
    'greater than or equal operator' => ['>=', ComparatorType::CT_GREATER_EQUAL],
    'less than operator' => ['<', ComparatorType::CT_LESS_THAN],
    'less than or equal operator' => ['<=', ComparatorType::CT_LESS_EQUAL],
]);

test('negation statement', function () {
    $attribute = Attribute::integer('value', 100);
    $attribute->toFormattedValue($buffer = new PlainbufferWriter);
    $condition = (new Condition(['comparison' => '=', 'column' => $attribute, 'logical' => 'and']))->not();
    expect($filter = $condition->toFilter())->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))
        ->and($filter->getCombinator())->toBeLogicalNot()
        ->and($filter->getSubFilters())->toHaveCount(1)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[0]))
        ->and($filter->getComparator())->toBeEqualityOperator()
        ->and($filter->getColumnName())->toBe($attribute->name())
        ->and($filter->getColumnValue())->toBe($buffer->getBuffer())
        ->and($filter->getFilterIfMissing())->toBeTrue()
        ->and($filter->getLatestVersionOnly())->toBeTrue();
});

test('could not generate filter from the attribute without value', function () {
    $attribute = Attribute::delete('value');
    $condition = new Condition(['comparison' => '=', 'column' => $attribute, 'logical' => 'and']);
    expect(fn () => $condition->toFilter())
        ->toThrow(InvalidArgumentException::class, 'The column [value] does not contain value.');
});
