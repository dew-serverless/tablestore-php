<?php

use Dew\Tablestore\Schema\Blueprint;

test('table can be created and deleted', function () {
    expect(tablestore()->hasTable('testing'))->toBeFalse();

    tablestore()->createTable('testing', function (Blueprint $table) {
        $table->string('key')->primary();
    });
    expect(tablestore()->hasTable('testing'))->toBeTrue();

    tablestore()->deleteTable('testing');
    expect(tablestore()->hasTable('testing'))->toBeFalse();
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('list lists all the tables', function () {
    tablestore()->createTable('list_testing_1', function ($table) {
        $table->string('key')->primary();
    });

    tablestore()->createTable('list_testing_2', function ($table) {
        $table->string('key')->primary();
    });

    $tables = tablestore()->listTable()->getTableNames()->getIterator();
    expect($tables)->toContain('list_testing_1');
    expect($tables)->toContain('list_testing_2');
    tablestore()->deleteTable('list_testing_1');
    tablestore()->deleteTable('list_testing_2');
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('get describes the table', function () {
    tablestore()->createTable('describe', function ($table) {
        $table->string('key')->primary();
    });

    $response = tablestore()->getTable('describe');
    expect($response->getTableMeta()->getTableName())->toBe('describe');

    tablestore()->deleteTable('describe');
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('update updates the table options', function () {
    tablestore()->createTable('update_options', function ($table) {
        $table->string('key')->primary();
        $table->ttl(86400);
        $table->maxVersions(1);
    });

    $response = tablestore()->updateTable('update_options', function ($table) {
        $table->maxVersions(10);
    });

    expect($response->getReservedThroughputDetails()->getCapacityUnit()->getRead())->toBe(0);
    expect($response->getReservedThroughputDetails()->getCapacityUnit()->getWrite())->toBe(0);
    expect($response->getTableOptions()->getTimeToLive())->toBe(86400);
    expect($response->getTableOptions()->getMaxVersions())->toBe(10);

    tablestore()->deleteTable('update_options');
})->skip(! integrationTestEnabled(), 'integration test not enabled');
