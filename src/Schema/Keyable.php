<?php

namespace Dew\Tablestore\Schema;

use Dew\Tablestore\Contracts\Schema;
use Protos\DefinedColumnSchema;
use Protos\PrimaryKeySchema;

class Keyable implements Schema
{
    /**
     * Determine if the column is a primary key.
     */
    public bool $isPrimaryKey = false;

    /**
     * Create a primary keyable column instance.
     */
    public function __construct(
        public Column $column,
        public int $type
    ) {
        //
    }

    /**
     * Indicate the column is a primary key.
     */
    public function primaryKey(): self
    {
        $this->isPrimaryKey = true;

        return $this;
    }

    /**
     * Represent the schema in Protobuf message.
     */
    public function toSchema(): PrimaryKeySchema|DefinedColumnSchema
    {
        if ($this->isPrimaryKey) {
            return (new PrimaryKeySchema)
                ->setName($this->column->name)
                ->setType($this->type);
        }

        return $this->column->toSchema();
    }
}
