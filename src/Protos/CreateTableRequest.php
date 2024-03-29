<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: table.proto

namespace Protos;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>acs.tablestore.table.CreateTableRequest</code>
 */
class CreateTableRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>required .acs.tablestore.table.TableMeta table_meta = 1;</code>
     */
    protected $table_meta = null;
    /**
     * Generated from protobuf field <code>required .acs.tablestore.table.ReservedThroughput reserved_throughput = 2;</code>
     */
    protected $reserved_throughput = null;
    /**
     * Generated from protobuf field <code>optional .acs.tablestore.table.TableOptions table_options = 3;</code>
     */
    protected $table_options = null;
    /**
     * Generated from protobuf field <code>repeated .acs.tablestore.table.PartitionRange partitions = 4;</code>
     */
    private $partitions;
    /**
     * Generated from protobuf field <code>optional .acs.tablestore.table.StreamSpecification stream_spec = 5;</code>
     */
    protected $stream_spec = null;
    /**
     * Generated from protobuf field <code>optional .acs.tablestore.table.SSESpecification sse_spec = 6;</code>
     */
    protected $sse_spec = null;
    /**
     * Generated from protobuf field <code>repeated .acs.tablestore.table.IndexMeta index_metas = 7;</code>
     */
    private $index_metas;
    /**
     * Generated from protobuf field <code>optional bool enable_local_txn = 8;</code>
     */
    protected $enable_local_txn = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Protos\TableMeta $table_meta
     *     @type \Protos\ReservedThroughput $reserved_throughput
     *     @type \Protos\TableOptions $table_options
     *     @type array<\Protos\PartitionRange>|\Google\Protobuf\Internal\RepeatedField $partitions
     *     @type \Protos\StreamSpecification $stream_spec
     *     @type \Protos\SSESpecification $sse_spec
     *     @type array<\Protos\IndexMeta>|\Google\Protobuf\Internal\RepeatedField $index_metas
     *     @type bool $enable_local_txn
     * }
     */
    public function __construct($data = NULL) {
        \Protos\Metadata\Table::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>required .acs.tablestore.table.TableMeta table_meta = 1;</code>
     * @return \Protos\TableMeta|null
     */
    public function getTableMeta()
    {
        return $this->table_meta;
    }

    public function hasTableMeta()
    {
        return isset($this->table_meta);
    }

    public function clearTableMeta()
    {
        unset($this->table_meta);
    }

    /**
     * Generated from protobuf field <code>required .acs.tablestore.table.TableMeta table_meta = 1;</code>
     * @param \Protos\TableMeta $var
     * @return $this
     */
    public function setTableMeta($var)
    {
        GPBUtil::checkMessage($var, \Protos\TableMeta::class);
        $this->table_meta = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>required .acs.tablestore.table.ReservedThroughput reserved_throughput = 2;</code>
     * @return \Protos\ReservedThroughput|null
     */
    public function getReservedThroughput()
    {
        return $this->reserved_throughput;
    }

    public function hasReservedThroughput()
    {
        return isset($this->reserved_throughput);
    }

    public function clearReservedThroughput()
    {
        unset($this->reserved_throughput);
    }

    /**
     * Generated from protobuf field <code>required .acs.tablestore.table.ReservedThroughput reserved_throughput = 2;</code>
     * @param \Protos\ReservedThroughput $var
     * @return $this
     */
    public function setReservedThroughput($var)
    {
        GPBUtil::checkMessage($var, \Protos\ReservedThroughput::class);
        $this->reserved_throughput = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>optional .acs.tablestore.table.TableOptions table_options = 3;</code>
     * @return \Protos\TableOptions|null
     */
    public function getTableOptions()
    {
        return $this->table_options;
    }

    public function hasTableOptions()
    {
        return isset($this->table_options);
    }

    public function clearTableOptions()
    {
        unset($this->table_options);
    }

    /**
     * Generated from protobuf field <code>optional .acs.tablestore.table.TableOptions table_options = 3;</code>
     * @param \Protos\TableOptions $var
     * @return $this
     */
    public function setTableOptions($var)
    {
        GPBUtil::checkMessage($var, \Protos\TableOptions::class);
        $this->table_options = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>repeated .acs.tablestore.table.PartitionRange partitions = 4;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getPartitions()
    {
        return $this->partitions;
    }

    /**
     * Generated from protobuf field <code>repeated .acs.tablestore.table.PartitionRange partitions = 4;</code>
     * @param array<\Protos\PartitionRange>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setPartitions($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Protos\PartitionRange::class);
        $this->partitions = $arr;

        return $this;
    }

    /**
     * Generated from protobuf field <code>optional .acs.tablestore.table.StreamSpecification stream_spec = 5;</code>
     * @return \Protos\StreamSpecification|null
     */
    public function getStreamSpec()
    {
        return $this->stream_spec;
    }

    public function hasStreamSpec()
    {
        return isset($this->stream_spec);
    }

    public function clearStreamSpec()
    {
        unset($this->stream_spec);
    }

    /**
     * Generated from protobuf field <code>optional .acs.tablestore.table.StreamSpecification stream_spec = 5;</code>
     * @param \Protos\StreamSpecification $var
     * @return $this
     */
    public function setStreamSpec($var)
    {
        GPBUtil::checkMessage($var, \Protos\StreamSpecification::class);
        $this->stream_spec = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>optional .acs.tablestore.table.SSESpecification sse_spec = 6;</code>
     * @return \Protos\SSESpecification|null
     */
    public function getSseSpec()
    {
        return $this->sse_spec;
    }

    public function hasSseSpec()
    {
        return isset($this->sse_spec);
    }

    public function clearSseSpec()
    {
        unset($this->sse_spec);
    }

    /**
     * Generated from protobuf field <code>optional .acs.tablestore.table.SSESpecification sse_spec = 6;</code>
     * @param \Protos\SSESpecification $var
     * @return $this
     */
    public function setSseSpec($var)
    {
        GPBUtil::checkMessage($var, \Protos\SSESpecification::class);
        $this->sse_spec = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>repeated .acs.tablestore.table.IndexMeta index_metas = 7;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getIndexMetas()
    {
        return $this->index_metas;
    }

    /**
     * Generated from protobuf field <code>repeated .acs.tablestore.table.IndexMeta index_metas = 7;</code>
     * @param array<\Protos\IndexMeta>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setIndexMetas($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Protos\IndexMeta::class);
        $this->index_metas = $arr;

        return $this;
    }

    /**
     * Generated from protobuf field <code>optional bool enable_local_txn = 8;</code>
     * @return bool
     */
    public function getEnableLocalTxn()
    {
        return isset($this->enable_local_txn) ? $this->enable_local_txn : false;
    }

    public function hasEnableLocalTxn()
    {
        return isset($this->enable_local_txn);
    }

    public function clearEnableLocalTxn()
    {
        unset($this->enable_local_txn);
    }

    /**
     * Generated from protobuf field <code>optional bool enable_local_txn = 8;</code>
     * @param bool $var
     * @return $this
     */
    public function setEnableLocalTxn($var)
    {
        GPBUtil::checkBool($var);
        $this->enable_local_txn = $var;

        return $this;
    }

}

