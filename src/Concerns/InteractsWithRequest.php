<?php

namespace Dew\Tablestore\Concerns;

use Dew\Tablestore\BatchBuilder;
use Dew\Tablestore\Builder;
use Protos\Condition;
use Protos\ReturnContent;

trait InteractsWithRequest
{
    /**
     * Build a condition Protobuf message.
     */
    protected function toCondition(BatchBuilder|Builder $builder): Condition
    {
        $condition = new Condition;
        $condition->setRowExistence($builder->expectation);

        return $condition;
    }

    /**
     * Build a return content Protobuf message.
     */
    protected function toReturnContent(BatchBuilder|Builder $builder): ReturnContent
    {
        $content = new ReturnContent;
        $content->setReturnType($builder->returned);
        $content->setReturnColumnNames($builder->selects);

        return $content;
    }
}
