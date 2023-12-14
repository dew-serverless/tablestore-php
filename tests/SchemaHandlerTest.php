<?php

use Dew\Tablestore\Schema\Blueprint;
use Dew\Tablestore\Schema\SchemaHandler;
use Dew\Tablestore\Tablestore;
use Protos\DefinedColumnType;
use Protos\PrimaryKeyOption;
use Protos\PrimaryKeyType;

test('primary key definition', function () {
    $table = new Blueprint;
    $table->integer('pk1')->primary();
    $table->string('pk2')->primary();
    $table->binary('pk3')->primary();
    $handler = new SchemaHandler(Mockery::mock(Tablestore::class));
    $pks = $handler->toTableMeta($table)->getPrimaryKey();
    expect($pks->count())->toBe(3)
        ->and($pks[0]->getName())->toBe('pk1')
        ->and($pks[0]->getType())->toBe(PrimaryKeyType::INTEGER)
        ->and($pks[1]->getName())->toBe('pk2')
        ->and($pks[1]->getType())->toBe(PrimaryKeyType::STRING)
        ->and($pks[2]->getName())->toBe('pk3')
        ->and($pks[2]->getType())->toBe(PrimaryKeyType::BINARY);
});

test('auto-increment integer primary key definition', function () {
    $table = new Blueprint;
    $table->autoIncrement('integer');
    $handler = new SchemaHandler(Mockery::mock(Tablestore::class));
    $pks = $handler->toTableMeta($table)->getPrimaryKey();
    expect($pks->count())->toBe(1)
        ->and($pks[0]->getName())->toBe('integer')
        ->and($pks[0]->getType())->toBe(PrimaryKeyType::INTEGER)
        ->and($pks[0]->getOption())->toBe(PrimaryKeyOption::AUTO_INCREMENT);
});

test('attribute column definition', function () {
    $table = new Blueprint;
    $table->integer('integer');
    $table->double('double');
    $table->boolean('boolean');
    $table->string('string');
    $table->binary('blob');
    $handler = new SchemaHandler(Mockery::mock(Tablestore::class));
    $cols = $handler->toTableMeta($table)->getDefinedColumn();
    expect($cols->count())->toBe(5)
        ->and($cols[0]->getName())->toBe('integer')
        ->and($cols[0]->getType())->toBe(DefinedColumnType::DCT_INTEGER)
        ->and($cols[1]->getName())->toBe('double')
        ->and($cols[1]->getType())->toBe(DefinedColumnType::DCT_DOUBLE)
        ->and($cols[2]->getName())->toBe('boolean')
        ->and($cols[2]->getType())->toBe(DefinedColumnType::DCT_BOOLEAN)
        ->and($cols[3]->getName())->toBe('string')
        ->and($cols[3]->getType())->toBe(DefinedColumnType::DCT_STRING)
        ->and($cols[4]->getName())->toBe('blob')
        ->and($cols[4]->getType())->toBe(DefinedColumnType::DCT_BLOB);
});
