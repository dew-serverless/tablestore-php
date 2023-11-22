<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: row_single.proto

namespace Protos;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>acs.tablestore.row.single.UpdateRowRequest</code>
 */
class UpdateRowRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>required string table_name = 1;</code>
     */
    protected $table_name = null;
    /**
     * Generated from protobuf field <code>required bytes row_change = 2;</code>
     */
    protected $row_change = null;
    /**
     * Generated from protobuf field <code>required .acs.tablestore.row.Condition condition = 3;</code>
     */
    protected $condition = null;
    /**
     * Generated from protobuf field <code>optional .acs.tablestore.row.ReturnContent return_content = 4;</code>
     */
    protected $return_content = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $table_name
     *     @type string $row_change
     *     @type \Protos\Condition $condition
     *     @type \Protos\ReturnContent $return_content
     * }
     */
    public function __construct($data = NULL) {
        \Protos\Metadata\RowSingle::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>required string table_name = 1;</code>
     * @return string
     */
    public function getTableName()
    {
        return isset($this->table_name) ? $this->table_name : '';
    }

    public function hasTableName()
    {
        return isset($this->table_name);
    }

    public function clearTableName()
    {
        unset($this->table_name);
    }

    /**
     * Generated from protobuf field <code>required string table_name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setTableName($var)
    {
        GPBUtil::checkString($var, True);
        $this->table_name = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>required bytes row_change = 2;</code>
     * @return string
     */
    public function getRowChange()
    {
        return isset($this->row_change) ? $this->row_change : '';
    }

    public function hasRowChange()
    {
        return isset($this->row_change);
    }

    public function clearRowChange()
    {
        unset($this->row_change);
    }

    /**
     * Generated from protobuf field <code>required bytes row_change = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setRowChange($var)
    {
        GPBUtil::checkString($var, False);
        $this->row_change = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>required .acs.tablestore.row.Condition condition = 3;</code>
     * @return \Protos\Condition|null
     */
    public function getCondition()
    {
        return $this->condition;
    }

    public function hasCondition()
    {
        return isset($this->condition);
    }

    public function clearCondition()
    {
        unset($this->condition);
    }

    /**
     * Generated from protobuf field <code>required .acs.tablestore.row.Condition condition = 3;</code>
     * @param \Protos\Condition $var
     * @return $this
     */
    public function setCondition($var)
    {
        GPBUtil::checkMessage($var, \Protos\Condition::class);
        $this->condition = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>optional .acs.tablestore.row.ReturnContent return_content = 4;</code>
     * @return \Protos\ReturnContent|null
     */
    public function getReturnContent()
    {
        return $this->return_content;
    }

    public function hasReturnContent()
    {
        return isset($this->return_content);
    }

    public function clearReturnContent()
    {
        unset($this->return_content);
    }

    /**
     * Generated from protobuf field <code>optional .acs.tablestore.row.ReturnContent return_content = 4;</code>
     * @param \Protos\ReturnContent $var
     * @return $this
     */
    public function setReturnContent($var)
    {
        GPBUtil::checkMessage($var, \Protos\ReturnContent::class);
        $this->return_content = $var;

        return $this;
    }

}

