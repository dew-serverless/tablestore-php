<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\Cells\BinaryAttribute;
use Dew\Tablestore\Cells\BooleanAttribute;
use Dew\Tablestore\Cells\DoubleAttribute;
use Dew\Tablestore\Cells\IntegerAttribute;
use Dew\Tablestore\Cells\StringAttribute;
use Dew\Tablestore\Cells\StringPrimaryKey;
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

    expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(0)
        ->and($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(1)
        ->and($row = $response->getDecodedRow())->toBeArray()->toHaveKey('key')
        ->and($row['key'])->toBeInstanceOf(StringPrimaryKey::class)
        ->and($row['key']->name())->toBe($key->name())
        ->and($row['key']->value())->toBe($key->value());
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('store data with timestamp', function () {
    $now = new DateTimeImmutable;
    $lastMinute = $now->sub(new DateInterval('PT1M'));

    $response = tablestore()->table('testing_items')->insert([
        PrimaryKey::string('key', 'timestamps'),
        Attribute::integer('value', 100)->setTimestamp($lastMinute),
        Attribute::integer('value', 200)->setTimestamp($now),
    ]);

    expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(0)
        ->and($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(1);
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('data can be retrieved', function () {
    $response = tablestore()->table('testing_items')->where([
        PrimaryKey::string('key', 'foo'),
    ])->get();

    $row = $response->getDecodedRow();

    expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(1)
        ->and($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(0)
        ->and($row)->toHaveKeys(['integer', 'double', 'true', 'false', 'string', 'binary'])
        ->and($row['integer'][0])->toBeInstanceOf(IntegerAttribute::class)
        ->and($row['integer'][0]->value())->toBe(100)
        ->and($row['double'][0])->toBeInstanceOf(DoubleAttribute::class)
        ->and($row['double'][0]->value())->toBe(3.14)
        ->and($row['true'][0])->toBeInstanceOf(BooleanAttribute::class)
        ->and($row['true'][0]->value())->toBe(true)
        ->and($row['false'][0])->toBeInstanceOf(BooleanAttribute::class)
        ->and($row['false'][0]->value())->toBe(false)
        ->and($row['string'][0])->toBeInstanceOf(StringAttribute::class)
        ->and($row['string'][0]->value())->toBe('foo')
        ->and($row['binary'][0])->toBeInstanceOf(BinaryAttribute::class)
        ->and($row['binary'][0]->value())->toBe('bar');
})->depends('data can be stored')->skip(! integrationTestEnabled(), 'integration test not enabled');

test('data retrieval with maximal versions', function () {
    $response = tablestore()->table('testing_items')->where([
        PrimaryKey::string('key', 'timestamps'),
    ])->take(2)->get();

    $row = $response->getDecodedRow();

    expect($row)->toBeArray()->toHaveKey('value')
        ->and($row['value'])->toBeArray()->toHaveCount(2)
        ->and($row['value'][0])->toBeInstanceOf(IntegerAttribute::class)
        ->and($row['value'][0]->value())->toBe(200)
        ->and($row['value'][1])->toBeInstanceOf(IntegerAttribute::class)
        ->and($row['value'][1]->value())->toBe(100);
})->depends('store data with timestamp')->skip(! integrationTestEnabled(), 'integration test not enabled');

test('data retrieval with selected columns', function () {
    $response = tablestore()->table('testing_items')
        ->where([PrimaryKey::string('key', 'foo')])
        ->select(['integer', 'string'])
        ->get();

    $row = $response->getDecodedRow();

    expect($row)->toBeArray()->toHaveKeys(['integer', 'string'])
        ->and($row)->not->toHaveKeys(['double', 'true', 'false', 'binary']);
})->depends('data can be stored')->skip(! integrationTestEnabled(), 'integration test not enabled');

test('data can be updated', function () {
    $response = tablestore()->table('testing_items')
        ->where([PrimaryKey::string('key', 'foo')])
        ->update([
            Attribute::integer('integer', 200),
            Attribute::double('double', 2.71828),
            Attribute::boolean('true', false),
            Attribute::boolean('false', true),
            Attribute::string('string', 'hello'),
            Attribute::binary('binary', 'world'),
        ]);

    expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(0)
        ->and($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(1)
        ->and($response->getDecodedRow())->toBeNull();
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('update with attribute version deletion', function () {
    $now = new DateTimeImmutable;
    $lastMinute = $now->sub(new DateInterval('PT1M'));

    // prepare the testing data
    tablestore()->table('testing_items')->insert([
        $key = PrimaryKey::string('key', 'test-delete-one-version'),
        Attribute::integer('integer', 100)->setTimestamp($lastMinute),
        Attribute::integer('integer', 200)->setTimestamp($now),
    ]);

    // delete the version "now"
    $response = tablestore()->table('testing_items')->where([$key])->update([
        Attribute::delete('integer')->version($now),
    ]);

    expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(0)
        ->and($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(1);

    // validate the version "now" is missing
    $response = tablestore()->table('testing_items')->where([$key])->get();
    $row = $response->getDecodedRow();

    expect($row)->toBeArray()->toHaveKey('integer')
        ->and($row['integer'])->toBeArray()->toHaveCount(1)
        ->and($row['integer'][0])->toBeInstanceOf(IntegerAttribute::class)
        ->and($row['integer'][0]->value())->toBe(100)
        ->and($row['integer'][0]->getTimestamp())->toBe((int) $lastMinute->format('Uv'));
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('update with attribute all versions deletion', function () {
    $now = new DateTimeImmutable;
    $lastMinute = $now->sub(new DateInterval('PT1M'));

    // prepare the testing data
    tablestore()->table('testing_items')->insert([
        $key = PrimaryKey::string('key', 'test-delete-all-versions'),
        Attribute::integer('integer', 100)->setTimestamp($lastMinute),
        Attribute::integer('integer', 200)->setTimestamp($now),
    ]);

    // delete all versions
    $response = tablestore()->table('testing_items')->where([$key])->update([
        Attribute::delete('integer')->all(),
    ]);

    expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(0)
        ->and($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(1);

    // validate the attribute is missing
    $response = tablestore()->table('testing_items')->where([$key])->get();
    $row = $response->getDecodedRow();

    expect($row)->toBeArray()->not->toHaveKey('integer');
})->skip(! integrationTestEnabled(), 'integration test not enabled');
