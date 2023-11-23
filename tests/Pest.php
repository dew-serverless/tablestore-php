<?php

use Dew\Tablestore\Builder;
use Dew\Tablestore\Tablestore;
use Protos\ColumnPaginationFilter;
use Protos\ComparatorType;
use Protos\CompositeColumnValueFilter;
use Protos\Filter;
use Protos\FilterType;
use Protos\LogicalOperator;
use Protos\SingleColumnValueFilter;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Let us take control of our Pest configurations and create a tidy and
| organized environment for our test cases. Don't forget that hooks
| could cover groups, folders, test cases, or even the whole app.
|
*/

uses()->group('integration')->in('Integration');

/*
|--------------------------------------------------------------------------
| Extensions
|--------------------------------------------------------------------------
|
| Pest has a variety of built-in assertion APIs to cover most of the use
| cases for testing an application. Do not hesitate to extend the API
| to let Pest sound and act more like a native in your application.
|
*/

expect()->extend('toBeSingleValueFilter', function () {
    $this->toBeInstanceOf(Filter::class);

    expect($this->value->getType())->toBe(FilterType::FT_SINGLE_COLUMN_VALUE);

    return $this;
});

expect()->extend('toBeCompositeValueFilter', function () {
    $this->toBeInstanceOf(Filter::class);

    expect($this->value->getType())->toBe(FilterType::FT_COMPOSITE_COLUMN_VALUE);

    return $this;
});

expect()->extend('toBeLogicalNot', fn () => $this->toBe(LogicalOperator::LO_NOT));
expect()->extend('toBeLogicalAnd', fn () => $this->toBe(LogicalOperator::LO_AND));
expect()->extend('toBeLogicalOr', fn () => $this->toBe(LogicalOperator::LO_OR));

expect()->extend('toBeEqualityOperator', fn () => $this->toBe(ComparatorType::CT_EQUAL));
expect()->extend('toBeInequalityOperator', fn () => $this->toBe(ComparatorType::CT_NOT_EQUAL));
expect()->extend('toBeGreaterThanOperator', fn () => $this->toBe(ComparatorType::CT_GREATER_THAN));
expect()->extend('toBeGreaterThanOrEqualOperator', fn () => $this->toBe(ComparatorType::CT_GREATER_EQUAL));
expect()->extend('toBeLessThanOperator', fn () => $this->toBe(ComparatorType::CT_LESS_THAN));
expect()->extend('toBeLessThanOrEqualOperator', fn () => $this->toBe(ComparatorType::CT_LESS_EQUAL));

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
|
| Extract the logic if you discover a repeated pattern, which helps you
| write expressive code and improve the maintainability. The defined
| helpers are unique globally and available in all the test files.
|
*/

/**
 * Determine if integration test cases should be run.
 */
function integrationTestEnabled(): bool
{
    $value = getenv('INTEGRATION_TEST_ENABLED');

    return filter_var($value, FILTER_VALIDATE_BOOL);
}

/**
 * Make a Tablestore client from environment.
 */
function tablestore(): Tablestore
{
    return new Tablestore(
        getenv('ACS_ACCESS_KEY_ID'), getenv('ACS_ACCESS_KEY_SECRET'),
        getenv('TS_ENDPOINT'), getenv('TS_INSTNACE')
    );
}

/**
 * Make a query against the given table on testing Tablestore instance.
 */
function table(string $table): Builder
{
    return tablestore()->table($table);
}

/**
 * Get the underlying column filter.
 */
function unwrapFilter(Filter $wrapper): SingleColumnValueFilter|CompositeColumnValueFilter|ColumnPaginationFilter
{
    $filter = match ($wrapper->getType()) {
        FilterType::FT_SINGLE_COLUMN_VALUE => new SingleColumnValueFilter,
        FilterType::FT_COMPOSITE_COLUMN_VALUE => new CompositeColumnValueFilter,
        FilterType::FT_COLUMN_PAGINATION => new ColumnPaginationFilter,
    };

    $filter->mergeFromString($wrapper->getFilter());

    return $filter;
}
