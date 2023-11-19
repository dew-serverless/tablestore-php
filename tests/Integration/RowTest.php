<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\PrimaryKey;

test('data can be stored', function () {
    $response = tablestore()->table('testing_items')->insert([
        $key = PrimaryKey::string('key', 'foo'),
        Attribute::integer('integer', 100),
        Attribute::double('double', 3.14),
        Attribute::boolean('true', true),
        Attribute::boolean('false', false),
        Attribute::string('string', 'foo'),
        Attribute::binary('binary', 'bar'),
    ]);

    expect($response)->toBeArray()
        ->and($response)->toHaveKeys(['consumed', 'row'])
        ->and($response['consumed']['capacity_unit']['read'])->toBe(0)
        ->and($response['consumed']['capacity_unit']['write'])->toBe(1)
        ->and($response['row']['key'])->toBeInstanceOf($key::class)
        ->and($response['row']['key']->name())->toBe($key->name())
        ->and($response['row']['key']->value())->toBe($key->value());
})->skip(! integrationTestEnabled(), 'integraion test not enabled');

test('store data with timestamp', function () {
    $now = new DateTimeImmutable;
    $lastMinute = $now->sub(new DateInterval('PT1M'));

    $response = tablestore()->table('testing_items')->insert([
        $key = PrimaryKey::string('key', 'timestamps'),
        Attribute::integer('value', 100)->setTimestamp($lastMinute),
        Attribute::integer('value', 200)->setTimestamp($now),
    ]);

    expect($response)->toBeArray()
        ->and($response)->toHaveKeys(['consumed', 'row'])
        ->and($response['consumed']['capacity_unit']['read'])->toBe(0)
        ->and($response['consumed']['capacity_unit']['write'])->toBe(1);
})->skip(! integrationTestEnabled(), 'integraion test not enabled');
