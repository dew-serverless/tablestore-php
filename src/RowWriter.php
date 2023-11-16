<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Cells\Cell;
use Dew\Tablestore\Cells\Tag;
use Dew\Tablestore\Contracts\CalculatesChecksum;

class RowWriter
{
    /**
     * Create a row buffer writer.
     */
    public function __construct(
        protected PlainbufferWriter $buffer,
        protected CalculatesChecksum $checksum
    ) {
        //
    }

    /**
     * Encode a buffer header.
     */
    public function writeHeader(): self
    {
        $this->buffer->writeLittleEndian32(Tag::HEADER);

        return $this;
    }

    /**
     * Encode the primary keys.
     *
     * pk = tag_pk cell_1 [cell_2] [cell_3]
     *
     * @param  \Dew\Tablestore\Cells\Cell[]  $cells
     */
    public function addPk(array $cells): self
    {
        // tag_pk
        $this->buffer->writeChar(Tag::PK);

        foreach ($cells as $cell) {
            $this->addCell($cell);
        }

        return $this;
    }

    /**
     * Encode the attributes.
     *
     * attr = tag_attr cell1 [cell_2] [cell_3]
     *
     * @param  \Dew\Tablestore\Cells\Cell[]  $cells
     */
    public function addAttr(array $cells): self
    {
        // tag_attr
        $this->buffer->writeChar(Tag::ATTR);

        foreach ($cells as $cell) {
            $this->addCell($cell);
        }

        return $this;
    }

    /**
     * Encode the cell.
     *
     * cell = tag_cell cell_name [cell_value] [cell_op] [cell_ts] cell_checksum
     */
    public function addCell(Cell $cell): self
    {
        $this->buffer->writeChar(Tag::CELL);

        $this->addCellName($cell);

        if ($cell->value() !== null) {
            $this->addCellValue($cell);
        }

        return $this->addCellChecksum($cell->getChecksumBy($this->checksum));
    }

    /**
     * Encode the cell name.
     *
     * cell_name = tag_cell_name formatted_value
     * formatted_value = value_len value_data
     * value_len = int32
     */
    public function addCellName(Cell $cell): self
    {
        // tag_cell_name
        $this->buffer->writeChar(Tag::CELL_NAME);

        // value_len
        $this->buffer->writeLittleEndian32(strlen($cell->name()));

        // value_data
        $this->buffer->write($cell->name());

        return $this;
    }

    /**
     * Encode the cell value.
     *
     * cell_value = tag_cell_value formatted_value
     */
    public function addCellValue(Cell $cell): self
    {
        $this->buffer->writeChar(Tag::CELL_VALUE);

        $cell->toFormattedValue($this->buffer);

        return $this;
    }

    /**
     * Encode the cell checksum.
     *
     * cell_checksum = tag_cell_checksum row_crc8
     */
    public function addCellChecksum(int $checksum): self
    {
        $this->buffer->writeChar(Tag::CELL_CHECKSUM);
        $this->buffer->writeChar($checksum);

        return $this;
    }

    /**
     * Get the row buffer.
     */
    public function getBuffer(): string
    {
        return $this->buffer->getBuffer();
    }
}
