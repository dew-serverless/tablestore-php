<?php

use Dew\Tablestore\PlainbufferReader;
use Dew\Tablestore\PlainbufferWriter;

test('buffer write and read', function () {
    $writer = (new PlainbufferWriter)->write('foo');
    $reader = new PlainbufferReader($writer->getBuffer());
    expect($reader->read(3))->toBe('foo');
});

test('buffer has size', function () {
    $writer = (new PlainbufferWriter)->write('foo');
    $reader = new PlainbufferReader($writer->getBuffer());
    expect($writer->size())->toBe(3)
        ->and($reader->size())->toBe(3);
});

test('unsigned long little endian 32', function () {
    $writer = (new PlainbufferWriter)->writeLittleEndian32(1);
    $reader = new PlainbufferReader($writer->getBuffer());
    expect($writer->size())->toBe(4)
        ->and($reader->readLittleEndian32())->toBe(1);
});

test('unsigned long little endian 64', function () {
    $writer = (new PlainbufferWriter)->writeLittleEndian64(1);
    $reader = new PlainbufferReader($writer->getBuffer());
    expect($writer->size())->toBe(8)
        ->and($reader->readLittleEndian64())->toBe(1);
});
