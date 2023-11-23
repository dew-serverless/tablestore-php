<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\Builder;
use Dew\Tablestore\Handler;
use Dew\Tablestore\PrimaryKey;
use Dew\Tablestore\Tablestore;
use Protos\Filter;

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
