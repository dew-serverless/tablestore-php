<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\Builder;
use Dew\Tablestore\Handler;
use Dew\Tablestore\PrimaryKey;
use Dew\Tablestore\Tablestore;
use GuzzleHttp\Psr7\Response;
use Protos\Filter;
use Protos\FilterType;

test('filter build determination primary keys', function () {
    $handler = new Handler(Mockery::mock(Tablestore::class));
    $builder = new Builder;
    $builder->whereKey(PrimaryKey::string('key', 'foo'));
    expect($handler->shouldBuildFilter($builder))->toBeFalse();
});

test('filter build determination attributes', function () {
    $handler = new Handler(Mockery::mock(Tablestore::class));
    $builder = new Builder;
    $builder->whereColumn(Attribute::string('attr1', 'foo'));
    expect($handler->shouldBuildFilter($builder))->toBeTrue();
});

test('filter build determination pagination', function () {
    $handler = new Handler(Mockery::mock(Tablestore::class));
    $builder = new Builder;
    $builder->whereKey([PrimaryKey::string('key', 'foo')])->offset(1, 1);
    expect($handler->shouldBuildFilter($builder))->toBeTrue();
});

test('filter build determination filter', function () {
    $handler = new Handler(Mockery::mock(Tablestore::class));
    $builder = new Builder;
    $builder->whereFilter(new Filter);
    expect($handler->shouldBuildFilter($builder))->toBeTrue();
});

test('build filter', function () {
    $handler = new Handler(Mockery::mock(Tablestore::class));
    $builder = new Builder;
    $builder->where('attr1', 'foo')->where('attr2', 'bar');

    expect($filter = $handler->buildFilter($builder))->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('build filter or', function () {
    $handler = new Handler(Mockery::mock(Tablestore::class));
    $builder = new Builder;
    $builder->where('attr1', 'foo')->orWhereColumn('attr2', 'bar');

    expect($filter = $handler->buildFilter($builder))->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('build filter group', function () {
    $handler = new Handler(Mockery::mock(Tablestore::class));
    $builder = new Builder;
    $builder->where('attr1', 'foo')->orWhere(function (Builder $builder) {
        $builder->where('attr2', 'bar')->where('attr3', 'baz');
    });

    expect($filter = $handler->buildFilter($builder))->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter))

        // expected: attr1 = 'foo' or (attr2 = 'bar' and attr3 = 'baz')
        ->and($filter->getCombinator())->toBeLogicalOr()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeCompositeValueFilter()
        ->and($filter = unwrapFilter($filter->getSubFilters()[1]))

        // expected: attr2 = 'bar' and attr3 = 'baz'
        ->and($filter->getCombinator())->toBeLogicalAnd()
        ->and($filter->getSubFilters())->toHaveCount(2)
        ->and($filter->getSubFilters()[0])->toBeSingleValueFilter()
        ->and($filter->getSubFilters()[1])->toBeSingleValueFilter();
});

test('condition update', function () {
    $filter = (new Filter)->setType(FilterType::FT_SINGLE_COLUMN_VALUE);
    $handler = new Handler(Mockery::mock(Tablestore::class));
    $builder = (new Builder)->handlerUsing($handler);
    $builder->setTable('test')
        ->whereKey([PrimaryKey::string('key', 'foo')])
        ->whereFilter($filter);
    expect($handler->toCondition($builder)->hasColumnCondition())->toBeTrue();
});

test('pagination filter', function () {
    $handler = new Handler(Mockery::mock(Tablestore::class));
    $builder = new Builder;
    $builder->whereKey([PrimaryKey::string('key', 'foo')])->offset(1, 1);
    expect($handler->buildFilter($builder))->toBePaginationFilter();
});

test('column condition has higher precedence than pagination', function () {
    $handler = new Handler(Mockery::mock(Tablestore::class));
    $builder = new Builder;
    $builder->whereKey([PrimaryKey::string('key', 'foo')])->whereColumn('value', 'bar')->offset(1, 1);
    expect($handler->buildFilter($builder))->toBeSingleValueFilter();
});

test('get row sends with max versions', function () {
    $mockedTs = Mockery::mock(Tablestore::class);
    $mockedTs->expects()
        ->send('/GetRow', Mockery::on(fn ($request) => $request->getMaxVersions() === 2))
        ->andReturns(new Response);
    $handler = new Handler($mockedTs);
    $builder = (new Builder)->setTable('test')->handlerUsing($handler);
    $builder->whereKey([PrimaryKey::string('key', 'foo')])->maxVersions(2)->get();
});

test('get row sends with default max versions 1', function () {
    $mockedTs = Mockery::mock(Tablestore::class);
    $mockedTs->expects()
        ->send('/GetRow', Mockery::on(fn ($request) => $request->getMaxVersions() === 1))
        ->andReturns(new Response);
    $handler = new Handler($mockedTs);
    $builder = (new Builder)->setTable('test')->handlerUsing($handler);
    $builder->whereKey([PrimaryKey::string('key', 'foo')])->get();
});

test('get row sends with time range', function () {
    $mockedTs = Mockery::mock(Tablestore::class);
    $mockedTs->expects()
        ->send('/GetRow', Mockery::on(fn ($request) => $request->hasTimeRange()))
        ->andReturns(new Response);
    $handler = new Handler($mockedTs);
    $builder = (new Builder)->setTable('test')->handlerUsing($handler);
    $builder->whereKey([PrimaryKey::string('key', 'foo')])->whereVersion(1234567891011)->get();
});
