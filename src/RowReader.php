<?php

namespace Dew\Tablestore;

use Dew\Tablestore\Cells\Cell;
use Dew\Tablestore\Cells\Tag;
use Dew\Tablestore\Contracts\CalculatesChecksum;
use Dew\Tablestore\Exceptions\RowReaderException;

class RowReader
{
    /**
     * The code instructs keep parsing.
     */
    protected const INSTRUCTOR_CONTINUE = 0;

    /**
     * The code instructs stop parsing.
     */
    protected const INSTRUCTOR_STOP = 1;

    /**
     * The pasring instructor.
     */
    protected int $instructor = self::INSTRUCTOR_CONTINUE;

    /**
     * The cell class.
     *
     * @var class-string
     */
    protected string $cellClass;

    /**
     * The cell data.
     *
     * @var array{
     *   name?: string,
     *   value?: mixed,
     *   class?: class-string<\Dew\Tablestore\Cells\Cell>,
     *   timestamp?: int
     * }
     */
    protected array $cell;

    /**
     * Determine if the row contains delete marker.
     */
    protected bool $hasDeleteMarker = false;

    /**
     * The row checksum.
     */
    protected int $rowChecksum = 0;

    /**
     * The decoded data.
     *
     * @var array<mixed>|null
     */
    protected ?array $data = null;

    public function __construct(
        protected PlainbufferReader $buffer,
        protected CalculatesChecksum $checksum
    ) {
        //
    }

    /**
     * Decode the buffer header.
     */
    protected function readHeader(): self
    {
        if ($this->buffer->readLittleEndian32() !== Tag::HEADER) {
            throw new RowReaderException('Seems like not a row buffer.');
        }

        return $this;
    }

    /**
     * Decode tag buffer.
     */
    protected function readTag(): ?int
    {
        if ($this->buffer->eof()) {
            $this->instructor = self::INSTRUCTOR_STOP;

            return null;
        }

        return $this->buffer->readChar();
    }

    /**
     * Decode primary key buffer.
     */
    protected function readPk(): self
    {
        return $this->enterPrimaryKeySection();
    }

    /**
     * Decode attribute buffer.
     */
    protected function readAttr(): self
    {
        return $this->enterAttributeSection();
    }

    /**
     * Mark entering the primary key section.
     */
    protected function enterPrimaryKeySection(): self
    {
        $this->cellClass = PrimaryKey::class;

        return $this;
    }

    /**
     * Mark entering the attribute section.
     */
    protected function enterAttributeSection(): self
    {
        $this->cellClass = Attribute::class;

        return $this;
    }

    /**
     * Decode cell buffer.
     */
    protected function readCell(): self
    {
        $this->cell = [];

        return $this;
    }

    /**
     * Decode cell name buffer.
     */
    protected function readCellName(): self
    {
        $this->cell['name'] = $this->buffer->read( // 2: read name by the size
            $this->buffer->readLittleEndian32()    // 1: get the name size
        );

        return $this;
    }

    /**
     * Decode cell value buffer.
     */
    protected function readCellValue(): self
    {
        $this->buffer->readLittleEndian32();

        $this->cell['class'] = $cellClass = $this->cellClass::classFromType($this->buffer->readChar());

        $this->cell['value'] = $cellClass::fromFormattedValue($this->buffer);

        return $this;
    }

    /**
     * Decode cell timestamp buffer.
     */
    protected function readCellTs(): self
    {
        $this->cell['timestamp'] = $this->buffer->readLittleEndian64();

        return $this;
    }

    /**
     * Decode cell checksum buffer.
     */
    protected function readCellChecksum(): self
    {
        $cell = $this->toCellInstance();
        $checksum = $cell->getChecksumBy($this->checksum);

        if ($this->buffer->readChar() !== $checksum) {
            throw new RowReaderException("Cell [{$cell->name()}] checksum mismatched.");
        }

        // When reaching the cell checksum tag, there is the last stage where
        // we can process the cell data. After validating the integrity of
        // the cell, we could confidently append it to the decoded data.
        $this->data[$cell->name()] = $cell;

        $this->rowChecksum = $this->checksum->char($checksum, $this->rowChecksum);

        return $this;
    }

    /**
     * Decode delete marker buffer.
     */
    protected function readDeleteMarker(): self
    {
        $this->hasDeleteMarker = true;

        return $this;
    }

    /**
     * Decode row checksum buffer.
     */
    protected function readRowChecksum(): self
    {
        // When reaching the row checksum tag, there's the end of the row
        // parsing phase. Before leaving, we do the final checksum for
        // the whole data payload. Wait, remember the delete marker?
        $this->rowChecksum = $this->checksum->char(
            (int) $this->hasDeleteMarker, $this->rowChecksum
        );

        if ($this->buffer->readChar() !== $this->rowChecksum) {
            throw new RowReaderException('Row checksum mismatched.');
        }

        return $this;
    }

    /**
     * Build cell instance from context.
     */
    protected function toCellInstance(): Cell
    {
        if (! isset($this->cell['class'], $this->cell['name'], $this->cell['value'])) {
            throw new RowReaderException('Could not build a cell instance from the incomplete data payload.');
        }

        $cell = new $this->cell['class']($this->cell['name'], $this->cell['value']);

        if ($cell instanceof Cells\Attribute && isset($this->cell['timestamp'])) {
            $cell->setTimestamp($this->cell['timestamp']);
        }

        return $cell;
    }

    /**
     * Handle the given tag.
     */
    protected function handle(?int $tag): self
    {
        // The tag could not be resolved from the upstream which could mean
        // the buffer is left empty. So, what about passing the torch on
        // to the handler to let it determines what we should do next?
        if ($tag === null) {
            return $this;
        }

        return match ($tag) {
            Tag::PK => $this->readPk(),
            Tag::ATTR => $this->readAttr(),
            Tag::CELL => $this->readCell(),
            Tag::CELL_NAME => $this->readCellName(),
            Tag::CELL_VALUE => $this->readCellValue(),
            Tag::CELL_TS => $this->readCellTs(),
            Tag::CELL_CHECKSUM => $this->readCellChecksum(),
            Tag::DELETE_MARKER => $this->readDeleteMarker(),
            Tag::ROW_CHECKSUM => $this->readRowChecksum(),
            default => throw new RowReaderException("Unexpected tag [$tag] occurred."),
        };
    }

    /**
     * Determine if we should keep parsing.
     */
    protected function shouldKeepParsing(): bool
    {
        return $this->instructor === self::INSTRUCTOR_CONTINUE;
    }

    /**
     * Decode the row buffer.
     */
    protected function decode(): void
    {
        $this->data = [];

        // Firstly, we try to decode the header that every well-formed row
        // Plainbuffer contains at the very beginning of the buffer. Or
        // else not need to waste the resources to parse the remains.
        $this->readHeader();

        while ($this->handle($this->readTag())->shouldKeepParsing()) {
            //
        }
    }

    /**
     * Decode the buffer into an array.
     *
     * @return array<mixed>
     */
    public function toArray(): array
    {
        if ($this->data === null) {
            $this->decode();
        }

        return $this->data; // @phpstan-ignore-line
    }

    /**
     * The buffer reader.
     */
    public function getBuffer(): PlainbufferReader
    {
        return $this->buffer;
    }
}
