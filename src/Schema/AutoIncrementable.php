<?php

namespace Dew\Tablestore\Schema;

use Dew\Tablestore\Contracts\Schema;
use Protos\DefinedColumnSchema;
use Protos\PrimaryKeyOption;
use Protos\PrimaryKeySchema;
use RuntimeException;

/**
 * @template TSchema of \Dew\Tablestore\Schema\Keyable
 *
 * @mixin TSchema
 */
class AutoIncrementable implements Schema
{
    /**
     * Determine if the column is auto-incrementing.
     */
    public bool $autoIncrementing = false;

    /**
     * Create an auto-incrementable column schema.
     *
     * @param  TSchema  $schema
     */
    public function __construct(
        public Schema $schema
    ) {
        //
    }

    /**
     * Indicate the column is auto-incrementing.
     *
     * @return \Dew\Tablestore\Schema\AutoIncrementable<TSchema>
     */
    public function autoIncrement(bool $autoIncrementing = true): self
    {
        $this->autoIncrementing = $autoIncrementing;

        if ($autoIncrementing) {
            $this->schema->primary();
        }

        return $this;
    }

    /**
     * Represent the schema in Protobuf message.
     */
    public function toSchema(): PrimaryKeySchema|DefinedColumnSchema
    {
        $schema = $this->schema->toSchema();

        if ($this->autoIncrementing && $schema instanceof PrimaryKeySchema) {
            $schema->setOption(PrimaryKeyOption::AUTO_INCREMENT);
        }

        return $schema;
    }

    /**
     * Redirect the calling to underlying schema.
     *
     * @param  array<int, mixed>  $arguments
     * @return $this
     */
    public function __call(string $method, array $arguments = []): self
    {
        $redirection = [$this->schema, $method];

        if (is_callable($redirection)) {
            call_user_func_array($redirection, $arguments);

            return $this;
        }

        throw new RuntimeException(sprintf('Call to undefined method %s::%s()',
            $this->schema::class, $method
        ));
    }
}
