<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: row_batch.proto

namespace Protos;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>acs.tablestore.row.batch.BatchWriteRowRequest</code>
 */
class BatchWriteRowRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>repeated .acs.tablestore.row.batch.TableInBatchWriteRowRequest tables = 1;</code>
     */
    private $tables;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array<\Protos\TableInBatchWriteRowRequest>|\Google\Protobuf\Internal\RepeatedField $tables
     * }
     */
    public function __construct($data = NULL) {
        \Protos\Metadata\RowBatch::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>repeated .acs.tablestore.row.batch.TableInBatchWriteRowRequest tables = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Generated from protobuf field <code>repeated .acs.tablestore.row.batch.TableInBatchWriteRowRequest tables = 1;</code>
     * @param array<\Protos\TableInBatchWriteRowRequest>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setTables($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Protos\TableInBatchWriteRowRequest::class);
        $this->tables = $arr;

        return $this;
    }

}

