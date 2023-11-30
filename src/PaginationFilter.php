<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Concerns\InteractsWithFilter;
use Protos\ColumnPaginationFilter;
use Protos\Filter;

class PaginationFilter
{
    use InteractsWithFilter;

    /**
     * Create a pagination filter builder.
     */
    public function __construct(
        protected int $offset,
        protected int $limit
    ) {
        //
    }

    /**
     * Build the Protobuf filter message.
     */
    public function toFilter(): Filter
    {
        $filter = new ColumnPaginationFilter;
        $filter->setOffset($this->offset);
        $filter->setLimit($this->limit);

        return $this->wrapFilter($filter);
    }
}
