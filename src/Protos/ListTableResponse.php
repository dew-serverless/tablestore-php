<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: table.proto

namespace Protos;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>acs.tablestore.table.ListTableResponse</code>
 */
class ListTableResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>repeated string table_names = 1;</code>
     */
    private $table_names;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array<string>|\Google\Protobuf\Internal\RepeatedField $table_names
     * }
     */
    public function __construct($data = NULL) {
        \Protos\Metadata\Table::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>repeated string table_names = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getTableNames()
    {
        return $this->table_names;
    }

    /**
     * Generated from protobuf field <code>repeated string table_names = 1;</code>
     * @param array<string>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setTableNames($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->table_names = $arr;

        return $this;
    }

}

