<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\Cells\Cell;
use Dew\Tablestore\Contracts\Attribute as AttributeContract;
use Dew\Tablestore\Contracts\PrimaryKey as PrimaryKeyContract;
use Dew\Tablestore\Exceptions\RowReaderException;
use Dew\Tablestore\PlainbufferReader;
use Dew\Tablestore\PlainbufferWriter;
use Dew\Tablestore\PrimaryKey;
use Dew\Tablestore\RowReader;
use Dew\Tablestore\RowWriter;
use Dew\Tablestore\Tests\Fixtures\MockedChecksum;

test('buffer should start with a header tag', function () {
    $buffer = (new PlainbufferWriter)->writeLittleEndian32(0);
    $reader = new RowReader(new PlainbufferReader($buffer->getBuffer()), new MockedChecksum);
    expect(fn () => $reader->toArray())->toThrow(RowReaderException::class, 'Seems like not a row buffer.');
});

it('has primary key', function ($pk) {
    $mockedChecksum = new MockedChecksum;
    $writer = new RowWriter(new PlainbufferWriter, $mockedChecksum);
    $writer->writeHeader()->addPk([$pk]);
    $reader = new RowReader(new PlainbufferReader($writer->getBuffer()), new MockedChecksum);
    $result = $reader->toArray();
    expect($result)->toBeArray()->and($result)->toHaveKey($pk->name());
    /** @var \Dew\Tablestore\Cells\Cell $column */
    $column = $result[$pk->name()];
    expect($column)->toBeInstanceOf(Cell::class)
        ->and($column)->toBeInstanceOf(PrimaryKeyContract::class)
        ->and($column->name())->toBe($pk->name())
        ->and($column->value())->toBe($pk->value())
        ->and($column->type())->toBe($pk->type());
})->with([
    'integer primary key' => [
        PrimaryKey::integer('key', 100),
    ],
    'string primary key' => [
        PrimaryKey::string('key', 'foo'),
    ],
    'binary primary key' => [
        PrimaryKey::binary('key', 'foo'),
    ],
]);

it('has attribute', function ($attr) {
    $mockedChecksum = new MockedChecksum;
    $writer = new RowWriter(new PlainbufferWriter, $mockedChecksum);
    $writer->writeHeader()->addAttr([$attr]);
    $reader = new RowReader(new PlainbufferReader($writer->getBuffer()), new MockedChecksum);
    $result = $reader->toArray();
    expect($result)->toBeArray()->and($result)->toHaveKey($attr->name());
    /** @var \Dew\Tablestore\Cells\Cell $cell */
    $cell = $result[$attr->name()][0];
    expect($cell)->toBeInstanceOf(Cell::class)
        ->and($cell)->toBeInstanceOf(AttributeContract::class)
        ->and($cell->name())->toBe($attr->name())
        ->and($cell->value())->toBe($attr->value())
        ->and($cell->type())->toBe($attr->type());
})->with([
    'integer attribute' => [
        Attribute::integer('value', 100),
    ],
    'double attribute' => [
        Attribute::double('value', 3.14),
    ],
    'boolean attribute true' => [
        Attribute::boolean('value', true),
    ],
    'boolean attribute false' => [
        Attribute::boolean('value', false),
    ],
    'string attribute' => [
        Attribute::string('value', 'foo'),
    ],
    'binary attribute' => [
        Attribute::binary('value', 'foo'),
    ],
]);

test('add row', function () {
    $mockedChecksum = new MockedChecksum;
    $writer = new RowWriter(new PlainbufferWriter, $mockedChecksum);
    $writer->writeHeader()->addRow([
        PrimaryKey::string('key', 'foo'),
        Attribute::string('value', 'bar'),
    ]);
    $reader = new RowReader(new PlainbufferReader($writer->getBuffer()), new MockedChecksum);
    $result = $reader->toArray();
    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['key', 'value'])
        ->and($result['key'])->toBeInstanceOf(PrimaryKeyContract::class)
        ->and($result['value'][0])->toBeInstanceOf(AttributeContract::class);
});

test('attribute with timestamp', function () {
    $mockedChecksum = new MockedChecksum;
    $writer = new RowWriter(new PlainbufferWriter, $mockedChecksum);
    $writer->writeHeader()->addRow([
        PrimaryKey::string('key', 'foo'),
        Attribute::string('value', 'bar')->setTimestamp($now = new DateTimeImmutable),
    ]);
    $reader = new RowReader(new PlainbufferReader($writer->getBuffer()), new MockedChecksum);
    $result = $reader->toArray();
    expect($result)->toBeArray()
        ->and($result)->toHaveKey('value')
        ->and($result['value'][0])->toBeInstanceOf(AttributeContract::class)
        ->and($result['value'][0]->getTimestamp())->toBe((int) $now->format('Uv'));
});
