<?php

namespace Dew\Tablestore\Concerns;

use Dew\Tablestore\BatchBuilder;
use Dew\Tablestore\Builder;
use Dew\Tablestore\ConditionFilter;
use Dew\Tablestore\PaginationFilter;
use Protos\Condition;
use Protos\Filter;
use Protos\ReturnContent;
use RuntimeException;

trait InteractsWithRequest
{
    /**
     * Build a condition Protobuf message.
     */
    public function toCondition(BatchBuilder|Builder $builder): Condition
    {
        $condition = new Condition;
        $condition->setRowExistence($builder->expectation);

        if ($this->shouldBuildFilter($builder)) {
            $condition->setColumnCondition($this->buildFilter($builder)->serializeToString());
        }

        return $condition;
    }

    /**
     * Build a return content Protobuf message.
     */
    public function toReturnContent(BatchBuilder|Builder $builder): ReturnContent
    {
        $content = new ReturnContent;
        $content->setReturnType($builder->returned);
        $content->setReturnColumnNames($builder->selects);

        return $content;
    }

    /**
     * Determine if the builder contains any filter conditions.
     */
    public function shouldBuildFilter(BatchBuilder|Builder $builder): bool
    {
        if ($builder->filter instanceof Filter) {
            return true;
        }

        if ($builder->wheres !== []) {
            return true;
        }

        return is_int($builder->offset) && is_int($builder->limit);
    }

    /**
     * Build Protobuf filter message from the builder.
     */
    public function buildFilter(BatchBuilder|Builder $builder): Filter
    {
        if ($builder->filter instanceof Filter) {
            return $builder->filter;
        }

        if ($builder->wheres !== []) {
            return (new ConditionFilter($builder->wheres))->toFilter();
        }

        if (is_int($builder->offset) && is_int($builder->limit)) {
            return (new PaginationFilter($builder->offset, $builder->limit))
                ->toFilter();
        }

        throw new RuntimeException('Missing filter data to build with.');
    }
}
