<?php

namespace Dew\Tablestore\Schema;

use Protos\CapacityUnit;
use Protos\DefinedColumnType;
use Protos\PrimaryKeyType;
use Protos\ReservedThroughput;
use Protos\SSEKeyType;
use Protos\SSESpecification;

class Blueprint
{
    /**
     * The table columns.
     *
     * @var \Dew\Tablestore\Contracts\Schema[]
     */
    public array $columns = [];

    /**
     * The throughput reservations.
     */
    public ?ReservedThroughput $throughput = null;

    /**
     * The number of seconds that data can exist.
     */
    public ?int $ttl = null;

    /**
     * The maximum versions to persist.
     */
    public ?int $maxVersions = null;

    /**
     * The version offset limit.
     */
    public ?int $versionOffset = null;

    /**
     * Determine if the existing row can be updated.
     */
    public ?bool $allowsUpdate = null;

    /**
     * Determine if the data should be encrypted.
     */
    public ?SSESpecification $encryption = null;

    /**
     * Define a new integer column.
     *
     * @return \Dew\Tablestore\Schema\AutoIncrementable<\Dew\Tablestore\Schema\Keyable>
     */
    public function integer(string $column): AutoIncrementable
    {
        return $this->columns[] = new AutoIncrementable(new Keyable(
            new Column($column, DefinedColumnType::DCT_INTEGER),
            PrimaryKeyType::INTEGER
        ));
    }

    /**
     * Define an auto-incrementing integer primary key column.
     *
     * @return \Dew\Tablestore\Schema\AutoIncrementable<\Dew\Tablestore\Schema\Keyable>
     */
    public function autoIncrement(string $column): AutoIncrementable
    {
        return $this->integer($column)->autoIncrement();
    }

    /**
     * Define a new double column.
     */
    public function double(string $column): Column
    {
        return $this->columns[] = new Column($column, DefinedColumnType::DCT_DOUBLE);
    }

    /**
     * Define a new boolean column.
     */
    public function boolean(string $column): Column
    {
        return $this->columns[] = new Column($column, DefinedColumnType::DCT_BOOLEAN);
    }

    /**
     * Define a new string column.
     */
    public function string(string $column): Keyable
    {
        return $this->columns[] = new Keyable(
            new Column($column, DefinedColumnType::DCT_STRING),
            PrimaryKeyType::STRING
        );
    }

    /**
     * Define a new binary column.
     */
    public function binary(string $column): Keyable
    {
        return $this->columns[] = new Keyable(
            new Column($column, DefinedColumnType::DCT_BLOB),
            PrimaryKeyType::BINARY
        );
    }

    /**
     * Reserve throughput for reading in capacity unit.
     *
     * @param  non-negative-int  $capacityUnit
     */
    public function reserveRead(int $capacityUnit): self
    {
        $cu = $this->throughput?->getCapacityUnit() ?? new CapacityUnit;
        $cu->setRead($capacityUnit);

        $this->throughput = (new ReservedThroughput)->setCapacityUnit($cu);

        return $this;
    }

    /**
     * Reserve throughput for writing in capacity unit.
     *
     * @param  non-negative-int  $capacityUnit
     */
    public function reserveWrite(int $capacityUnit): self
    {
        $cu = $this->throughput?->getCapacityUnit() ?? new CapacityUnit;
        $cu->setWrite($capacityUnit);

        $this->throughput = (new ReservedThroughput)->setCapacityUnit($cu);

        return $this;
    }

    /**
     * Set the number of seconds that data can exist.
     */
    public function ttl(int $seconds): self
    {
        $this->ttl = $seconds;

        return $this;
    }

    /**
     * Store the data permanently.
     */
    public function forever(): self
    {
        return $this->ttl(-1);
    }

    /**
     * Set the maximum versions to persist.
     */
    public function maxVersions(int $versions): self
    {
        $this->maxVersions = $versions;

        return $this;
    }

    /**
     * Set the version offset limit in seconds.
     */
    public function versionOffsetIn(int $seconds): self
    {
        $this->versionOffset = $seconds;

        return $this;
    }

    /**
     * Allow existing rows to be updated.
     */
    public function allowUpdate(bool $allows = true): self
    {
        $this->allowsUpdate = $allows;

        return $this;
    }

    /**
     * Indicate the data should be encrypted.
     */
    public function encryptWithKms(): self
    {
        $this->encryption = (new SSESpecification)
            ->setEnable(true)
            ->setKeyType(SSEKeyType::SSE_KMS_SERVICE);

        return $this;
    }

    /**
     * Indicate the data should be encrypted using the given key.
     *
     * @param  string  $key  The KMS Key ID.
     * @param  string  $role  The ARN of the role that could use the key.
     */
    public function encryptWith(string $key, string $role): self
    {
        $this->encryption = (new SSESpecification)
            ->setEnable(true)
            ->setKeyType(SSEKeyType::SSE_BYOK)
            ->setKeyId($key)
            ->setRoleArn($role);

        return $this;
    }

    /**
     * Indicate the data should not be encrypted.
     */
    public function withoutEncryption(): self
    {
        $this->encryption = null;

        return $this;
    }
}
