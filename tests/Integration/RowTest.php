<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\Cells\BinaryAttribute;
use Dew\Tablestore\Cells\BooleanAttribute;
use Dew\Tablestore\Cells\DoubleAttribute;
use Dew\Tablestore\Cells\IntegerAttribute;
use Dew\Tablestore\Cells\StringAttribute;
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

test('data can be retrieved', function () {
    $response = tablestore()->table('testing_items')->where([
        PrimaryKey::string('key', 'foo'),
    ])->get();

    expect($response)->toBeArray()
        ->and($response)->toHaveKeys(['consumed', 'row'])
        ->and($response['consumed']['capacity_unit']['read'])->toBe(1)
        ->and($response['consumed']['capacity_unit']['write'])->toBe(0)
        ->and($response['row'])->toHaveKeys(['integer', 'double', 'true', 'false', 'string', 'binary'])
        ->and($response['row']['integer'][0])->toBeInstanceOf(IntegerAttribute::class)
        ->and($response['row']['integer'][0]->value())->toBe(100)
        ->and($response['row']['double'][0])->toBeInstanceOf(DoubleAttribute::class)
        ->and($response['row']['double'][0]->value())->toBe(3.14)
        ->and($response['row']['true'][0])->toBeInstanceOf(BooleanAttribute::class)
        ->and($response['row']['true'][0]->value())->toBe(true)
        ->and($response['row']['false'][0])->toBeInstanceOf(BooleanAttribute::class)
        ->and($response['row']['false'][0]->value())->toBe(false)
        ->and($response['row']['string'][0])->toBeInstanceOf(StringAttribute::class)
        ->and($response['row']['string'][0]->value())->toBe('foo')
        ->and($response['row']['binary'][0])->toBeInstanceOf(BinaryAttribute::class)
        ->and($response['row']['binary'][0]->value())->toBe('bar');
})->depends('data can be stored')->skip(! integrationTestEnabled(), 'integraion test not enabled');

test('data retrieval with maximal versions', function () {
    $response = tablestore()->table('testing_items')->where([
        PrimaryKey::string('key', 'timestamps'),
    ])->take(2)->get();

    expect($response)->toBeArray()
        ->and($response)->toHaveKeys(['consumed', 'row'])
        ->and($response['row']['value'])->toBeArray()->toHaveCount(2)
        ->and($response['row']['value'][0])->toBeInstanceOf(IntegerAttribute::class)
        ->and($response['row']['value'][0]->value())->toBe(200)
        ->and($response['row']['value'][1])->toBeInstanceOf(IntegerAttribute::class)
        ->and($response['row']['value'][1]->value())->toBe(100);
})->depends('store data with timestamp')->skip(! integrationTestEnabled(), 'integraion test not enabled');

test('data retrieval with selected columns', function () {
    $response = tablestore()->table('testing_items')
        ->where([PrimaryKey::string('key', 'foo')])
        ->select(['integer', 'string'])
        ->get();

    expect($response)->toBeArray()->toHaveKeys(['consumed', 'row'])
        ->and($response['row'])->toHaveKeys(['integer', 'string'])
        ->and($response['row'])->not->toHaveKeys(['double', 'true', 'false', 'binary']);
})->depends('data can be stored')->skip(! integrationTestEnabled(), 'integraion test not enabled');
