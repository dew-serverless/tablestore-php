<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\BatchBag;
use Dew\Tablestore\BatchHandler;
use Dew\Tablestore\PrimaryKey;
use Dew\Tablestore\Tablestore;
use Protos\ReturnType;
use Protos\RowExistenceExpectation;

test('read retrieves all columns by default', function () {
    $bag = new BatchBag;
    $bag->table('testing')->where([PrimaryKey::string('key', 'foo')])->get();
    $bag->table('testing')->where([PrimaryKey::string('key', 'bar')])->get();
    $handler = new BatchHandler(Mockery::mock(Tablestore::class));
    $tables = $handler->buildReadTables($bag);
    expect($tables[0]->getColumnsToGet()->count())->toBe(0);
});

test('read merges selected columns', function () {
    $bag = new BatchBag;
    $bag->table('testing')->where([PrimaryKey::string('key', 'foo')])->select(['key'])->get();
    $bag->table('testing')->where([PrimaryKey::string('key', 'bar')])->select(['value'])->get();
    $handler = new BatchHandler(Mockery::mock(Tablestore::class));
    $tables = $handler->buildReadTables($bag);
    expect($tables[0]->getColumnsToGet()->count())->toBe(2)
        ->and($tables[0]->getColumnsToGet()[0])->toBe('key')
        ->and($tables[0]->getColumnsToGet()[1])->toBe('value');
});

test('read retrieves at most one value version by default', function () {
    $bag = new BatchBag;
    $bag->table('testing')->where([PrimaryKey::string('key', 'foo')])->get();
    $bag->table('testing')->where([PrimaryKey::string('key', 'bar')])->get();
    $handler = new BatchHandler(Mockery::mock(Tablestore::class));
    $tables = $handler->buildReadTables($bag);
    expect($tables[0]->getMaxVersions())->toBe(1);
});

test('read calculates the max value version', function () {
    $bag = new BatchBag;
    $bag->table('testing')->where([PrimaryKey::string('key', 'foo')])->take(3)->get();
    $bag->table('testing')->where([PrimaryKey::string('key', 'bar')])->take(2)->get();
    $handler = new BatchHandler(Mockery::mock(Tablestore::class));
    $tables = $handler->buildReadTables($bag);
    expect($tables[0]->getMaxVersions())->toBe(3);
});

test('write with row expectation', function () {
    $bag = new BatchBag;
    $bag->table('testing')->where([
        PrimaryKey::string('key', 'foo'),
    ])->expectExists()->update([
        Attribute::string('value', 'bar'),
    ]);
    $handler = new BatchHandler(Mockery::mock(Tablestore::class));
    $tables = $handler->buildWriteTables($bag);
    expect($tables[0]->getRows()[0]->getCondition()->getRowExistence())->toBe(RowExistenceExpectation::EXPECT_EXIST);
});

test('write with returned row customization', function () {
    $bag = new BatchBag;
    $bag->table('testing')->where([
        PrimaryKey::string('key', 'foo'),
    ])->returnModified()->update([
        Attribute::string('value', 'bar'),
    ]);
    $handler = new BatchHandler(Mockery::mock(Tablestore::class));
    $tables = $handler->buildWriteTables($bag);
    expect($tables[0]->getRows()[0]->getReturnContent()->getReturnType())->toBe(ReturnType::RT_AFTER_MODIFY);
});
