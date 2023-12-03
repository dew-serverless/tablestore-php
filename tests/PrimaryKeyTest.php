<?php

use Dew\Tablestore\Cells\IntegerPrimaryKey;
use Dew\Tablestore\Cells\StringPrimaryKey;
use Dew\Tablestore\PrimaryKey;

test('integer primary key resolution', function () {
    $primaryKey = PrimaryKey::createFromValue('key', 1);
    expect($primaryKey)->toBeInstanceOf(IntegerPrimaryKey::class)
        ->and($primaryKey->name())->toBe('key')
        ->and($primaryKey->value())->toBe(1);
});

test('string primary key resolution', function () {
    $primaryKey = PrimaryKey::createFromValue('key', 'foo');
    expect($primaryKey)->toBeInstanceOf(StringPrimaryKey::class)
        ->and($primaryKey->name())->toBe('key')
        ->and($primaryKey->value())->toBe('foo');
});

test('unsupported primary key resolution', function () {
    expect(fn () => PrimaryKey::createFromValue('key', null))
        ->toThrow(InvalidArgumentException::class, 'Could not build a primary key from the [NULL] type.');
});
