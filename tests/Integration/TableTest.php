<?php

use Dew\Tablestore\Schema\Blueprint;

test('table can be created and deleted', function () {
    tablestore()->createTable('testing', function (Blueprint $table) {
        $table->string('key')->primary();
        $table->encryptWithKms();
    });

    tablestore()->deleteTable('testing');
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
