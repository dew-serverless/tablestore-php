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

test('table option configures time-to-live', function () {
    $table = (new Blueprint)->ttl(86400);
    $handler = new SchemaHandler(Mockery::mock(Tablestore::class));
    expect($handler->toTableOptions($table)->getTimeToLive())->toBe(86400);
});

test('table option configures data that is stored permanently', function () {
    $table = (new Blueprint)->forever();
    $handler = new SchemaHandler(Mockery::mock(Tablestore::class));
    expect($handler->toTableOptions($table)->getTimeToLive())->toBe(-1);
});

test('table option defines max versions to persist', function () {
    $table = (new Blueprint)->maxVersions(2);
    $handler = new SchemaHandler(Mockery::mock(Tablestore::class));
    expect($handler->toTableOptions($table)->getMaxVersions())->toBe(2);
});

test('table option limits version offset', function () {
    $table = (new Blueprint)->versionOffsetIn(86400 * 2); // 2 days
    $handler = new SchemaHandler(Mockery::mock(Tablestore::class));
    expect($handler->toTableOptions($table)->getDeviationCellVersionInSec())->toBe(86400 * 2);
});

test('table option allows update', function () {
    $table = (new Blueprint)->allowUpdate();
    $handler = new SchemaHandler(Mockery::mock(Tablestore::class));
    expect($handler->toTableOptions($table)->getAllowUpdate())->toBeTrue();
});

test('table option denies update', function () {
    $table = (new Blueprint)->allowUpdate(false);
    $handler = new SchemaHandler(Mockery::mock(Tablestore::class));
    expect($handler->toTableOptions($table)->getAllowUpdate())->toBeFalse();
});

test('table options modify determination', function () {
    $handler = new SchemaHandler(Mockery::mock(Tablestore::class));
    expect($handler->hasTableOptionsUpdate(new Blueprint))->toBeFalse();
    expect($handler->hasTableOptionsUpdate((new Blueprint)->ttl(86400 * 30)))->toBeTrue();
    expect($handler->hasTableOptionsUpdate((new Blueprint)->maxVersions(10)))->toBeTrue();
    expect($handler->hasTableOptionsUpdate((new Blueprint)->versionOffsetIn(86400)))->toBeTrue();
    expect($handler->hasTableOptionsUpdate((new Blueprint)->allowUpdate(true)))->toBeTrue();
    expect($handler->hasTableOptionsUpdate((new Blueprint)->allowUpdate(false)))->toBeTrue();
});
