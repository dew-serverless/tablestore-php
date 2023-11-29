<?php

namespace Dew\Tablestore\Concerns;

use Dew\Tablestore\BatchBuilder;
use Dew\Tablestore\Builder;
use Dew\Tablestore\FilterBuilder;
use Protos\Condition;
use Protos\Filter;
use Protos\ReturnContent;

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

        return $builder->wheres !== [];
    }

    /**
     * Build Protobuf filter message from the builder.
     */
    public function buildFilter(BatchBuilder|Builder $builder): Filter
    {
        if ($builder->filter instanceof Filter) {
            return $builder->filter;
        }

        return (new FilterBuilder($builder->wheres))->toFilter();
    }
}
