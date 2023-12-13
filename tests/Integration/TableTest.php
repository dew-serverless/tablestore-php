<?php

use Dew\Tablestore\Schema\Blueprint;

test('table can be created and deleted', function () {
    tablestore()->createTable('testing', function (Blueprint $table) {
        $table->string('key')->primary();
        $table->encryptWithKms();
    });

    tablestore()->deleteTable('testing');
})->skip(! integrationTestEnabled(), 'integration test not enabled');
