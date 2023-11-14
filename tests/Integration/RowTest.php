<?php

beforeEach(function () {
    //
})->skip(! integrationTestEnabled(), 'integraion test not enabled');

test('data can be stored', function () {
    $response = tablestore()->table('testing_items')->insert([
        // PrimaryKey::string('key', 'foo'),
        // Attribute::string('value', 'bar'),
    ]);

    expect($response)->toBeArray();
});
