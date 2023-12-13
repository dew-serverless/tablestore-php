<?php

namespace Dew\Tablestore\Schema;

use Dew\Tablestore\Contracts\Schema;
use Protos\DefinedColumnSchema;
use Protos\PrimaryKeySchema;

class Column implements Schema
{
    /**
     * Create a new schema column instance.
     */
    public function __construct(
        public string $name,
        public int $type
    ) {
        //
    }

    /**
     * Represent the schema in Protobuf message.
     */
    public function toSchema(): PrimaryKeySchema|DefinedColumnSchema
    {
        return (new DefinedColumnSchema)
            ->setName($this->name)
            ->setType($this->type);
    }
}
