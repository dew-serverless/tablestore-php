<?php

use Dew\Tablestore\PaginationFilter;

test('filter generation', function () {
    $builder = new PaginationFilter(2, 1);
    expect($filter = $builder->toFilter())->toBePaginationFilter()
        ->and($filter = unwrapFilter($filter))
        ->and($filter->getOffset())->toBe(2)
        ->and($filter->getLimit())->toBe(1);
});
