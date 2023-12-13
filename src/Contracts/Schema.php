<?php

namespace Dew\Tablestore\Contracts;

use Protos\DefinedColumnSchema;
use Protos\PrimaryKeySchema;

interface Schema
{
    /**
     * Represent the schema in Protobuf message.
     */
    public function toSchema(): PrimaryKeySchema|DefinedColumnSchema;
}
