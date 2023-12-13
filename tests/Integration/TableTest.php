<?php

use Dew\Tablestore\Schema\Blueprint;

test('table can be created', function () {
    tablestore()->createTable('testing', function (Blueprint $table) {
        $table->string('key')->primaryKey();
        $table->encryptWithKms();
    });
})->skip(! integrationTestEnabled(), 'integration test not enabled');
