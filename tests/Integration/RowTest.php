<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\Cells\BinaryAttribute;
use Dew\Tablestore\Cells\BooleanAttribute;
use Dew\Tablestore\Cells\DoubleAttribute;
use Dew\Tablestore\Cells\IntegerAttribute;
use Dew\Tablestore\Cells\StringAttribute;
use Dew\Tablestore\Cells\StringPrimaryKey;
use Dew\Tablestore\PrimaryKey;
use Dew\Tablestore\Responses\RowDecodableResponse;

test('data can be stored', function () {
    $response = tablestore()->table('rows')->insert([
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

    $response = tablestore()->table('rows')->insert([
        PrimaryKey::string('key', 'timestamps'),
        Attribute::integer('value', 100)->setTimestamp($lastMinute),
        Attribute::integer('value', 200)->setTimestamp($now),
    ]);

    expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(0)
        ->and($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(1);
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('data can be retrieved', function () {
    $response = tablestore()->table('rows')->where([
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
    $response = tablestore()->table('rows')->where([
        PrimaryKey::string('key', 'timestamps'),
    ])->maxVersions(2)->get();

    $row = $response->getDecodedRow();

    expect($row)->toBeArray()->toHaveKey('value')
        ->and($row['value'])->toBeArray()->toHaveCount(2)
        ->and($row['value'][0])->toBeInstanceOf(IntegerAttribute::class)
        ->and($row['value'][0]->value())->toBe(200)
        ->and($row['value'][1])->toBeInstanceOf(IntegerAttribute::class)
        ->and($row['value'][1]->value())->toBe(100);
})->depends('store data with timestamp')->skip(! integrationTestEnabled(), 'integration test not enabled');

test('data retrieval with selected columns', function () {
    $response = tablestore()->table('rows')
        ->where([PrimaryKey::string('key', 'foo')])
        ->select(['integer', 'string'])
        ->get();

    $row = $response->getDecodedRow();

    expect($row)->toBeArray()->toHaveKeys(['integer', 'string'])
        ->and($row)->not->toHaveKeys(['double', 'true', 'false', 'binary']);
})->depends('data can be stored')->skip(! integrationTestEnabled(), 'integration test not enabled');

test('data can be updated', function () {
    $response = tablestore()->table('rows')
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
    tablestore()->table('rows')->insert([
        $key = PrimaryKey::string('key', 'test-delete-one-version'),
        Attribute::integer('integer', 100)->setTimestamp($lastMinute),
        Attribute::integer('integer', 200)->setTimestamp($now),
    ]);

    // delete the version "now"
    $response = tablestore()->table('rows')->where([$key])->update([
        Attribute::delete('integer')->version($now),
    ]);

    expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(0)
        ->and($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(1);

    // validate the version "now" is missing
    $response = tablestore()->table('rows')->where([$key])->get();
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
    tablestore()->table('rows')->insert([
        $key = PrimaryKey::string('key', 'test-delete-all-versions'),
        Attribute::integer('integer', 100)->setTimestamp($lastMinute),
        Attribute::integer('integer', 200)->setTimestamp($now),
    ]);

    // delete all versions
    $response = tablestore()->table('rows')->where([$key])->update([
        Attribute::delete('integer')->all(),
    ]);

    expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(0)
        ->and($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(1);

    // validate the attribute is missing
    $response = tablestore()->table('rows')->where([$key])->get();
    $row = $response->getDecodedRow();

    expect($row)->toBeArray()->not->toHaveKey('integer');
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('update with increment operation', function () {
    // prepare the testing data
    tablestore()->table('rows')->insert([
        $key = PrimaryKey::string('key', 'test-counter'),
        Attribute::integer('value', 0),
    ]);

    // apply increment operation
    $increment = function ($key): void {
        $response = tablestore()->table('rows')->where([$key])->update([
            Attribute::integer('value', 1)->increment(),
        ]);

        expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(1)
            ->and($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(1);
    };

    // apply one more time to ensure we're not only overriding the value
    $increment($key);
    $increment($key);

    // validate the incremented value
    $response = tablestore()->table('rows')->where([$key])->get();
    $row = $response->getDecodedRow();

    expect($row)->toBeArray()->toHaveKey('value')
        ->and($row['value'][0])->toBeInstanceOf(IntegerAttribute::class)
        ->and($row['value'][0]->value())->toBe(2);
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('delete removes the row', function () {
    // prepare the testing data
    tablestore()->table('rows')->insert([
        $key = PrimaryKey::string('key', 'test-delete'),
        Attribute::string('value', 'foo'),
    ]);

    // ensure the data is existing
    $response = tablestore()->table('rows')->where([$key])->get();
    expect($response->getDecodedRow())->toBeArray()->toHaveKey('value');

    // delete the row
    $response = tablestore()->table('rows')->where([$key])->delete();
    expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(0)
        ->and($response->getConsumed()->getCapacityUnit()->getRead())->toBe(0);

    // ensure the data is missing
    $response = tablestore()->table('rows')->where([$key])->get();
    expect($response->getDecodedRow())->toBeNull();
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('batch write writes multiple rows', function () {
    $response = tablestore()->batch(function ($builder) {
        $builder->table('rows')->insert([
            PrimaryKey::string('key', 'batch-write-1'),
            Attribute::string('value', 'foo'),
        ]);

        $builder->table('rows')->insert([
            PrimaryKey::string('key', 'batch-write-2'),
            Attribute::string('value', 'foo'),
        ]);
    });

    expect($response->getTables()[0]->getPutRows()[0]->getIsOk())->toBeTrue()
        ->and($response->getTables()[0]->getPutRows()[0]->getIsOk())->toBeTrue();
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('batch write updates multiple rows', function () {
    $response = tablestore()->batch(function ($builder) {
        $builder->table('rows')->where([
            PrimaryKey::string('key', 'batch-write-1'),
        ])->update([
            Attribute::string('value', 'foo-new'),
        ]);

        $builder->table('rows')->where([
            PrimaryKey::string('key', 'batch-write-2'),
        ])->update([
            Attribute::string('value', 'foo-new'),
        ]);
    });

    expect($response->getTables()[0]->getPutRows()[0]->getIsOk())->toBeTrue()
        ->and($response->getTables()[0]->getPutRows()[1]->getIsOk())->toBeTrue();
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('batch write increments counter', function () {
    // prepare the testing data
    $response = tablestore()->table('rows')->insert([
        $key = PrimaryKey::string('key', 'batch-counter'),
        Attribute::integer('value', 0),
    ]);

    expect($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(1);

    // apply increment operation
    $increment = function ($key): void {
        $response = tablestore()->batch(function ($builder) use ($key) {
            $builder->table('rows')->where([$key])->update([
                Attribute::integer('value', 1)->increment(),
            ]);
        });

        expect($response->getTables()->count())->toBe(1)
            ->and($response->getTables()[0]->getPutRows()->count())->toBe(1)
            ->and($response->getTables()[0]->getPutRows()[0]->getIsOk())->toBeTrue();
    };

    // apply one more time to ensure we're not only overriding the value
    $increment($key);
    $increment($key);

    // validate the incremented value
    $response = tablestore()->table('rows')->where([$key])->get();
    $row = $response->getDecodedRow();

    expect($row)->toBeArray()->toHaveKey('value')
        ->and($row['value'][0])->toBeInstanceOf(IntegerAttribute::class)
        ->and($row['value'][0]->value())->toBe(2);
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('batch write deletes multiple rows', function () {
    $response = tablestore()->batch(function ($builder) {
        $builder->table('rows')->where([
            PrimaryKey::string('key', 'batch-write-1'),
        ])->delete();

        $builder->table('rows')->where([
            PrimaryKey::string('key', 'batch-write-2'),
        ])->delete();
    });

    expect($response->getTables()[0]->getPutRows()[0]->getIsOk())->toBeTrue()
        ->and($response->getTables()[0]->getPutRows()[1]->getIsOk())->toBeTrue();
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('batch read retrieves multiple rows', function () {
    // prepare the testing data
    $pk1 = PrimaryKey::string('key', 'batch-read-1');
    $attr1 = Attribute::string('value', 'foo');
    $pk2 = PrimaryKey::string('key', 'batch-read-2');
    $attr2 = Attribute::string('value', 'bar');

    $response = tablestore()->batch(function ($builder) use ($pk1, $attr1, $pk2, $attr2) {
        $builder->table('rows')->insert([$pk1, $attr1]);
        $builder->table('rows')->insert([$pk2, $attr2]);
    });

    expect($response->getTables()->count())->toBe(1);

    // validate the results
    $response = tablestore()->batch(function ($builder) use ($pk1, $pk2) {
        $builder->table('rows')->where([$pk1])->get();
        $builder->table('rows')->where([$pk2])->get();
    });

    expect($response->getTables()->count())->toBe(1)
        ->and($response->getTables()[0]->getTableName())->toBe('rows')
        ->and($response->getTables()[0]->getRows()->count())->toBe(2)
        ->and($response->getTables()[0]->getRows()[0]->getIsOk())->toBeTrue()
        ->and($response->getTables()[0]->getRows()[1]->getIsOk())->toBeTrue();

    $row1 = (new RowDecodableResponse($response->getTables()[0]->getRows()[0]))->getDecodedRow();
    expect($row1)->toBeArray()->toHaveKeys(['key', 'value'])
        ->and($row1['key']->name())->toBe($pk1->name())
        ->and($row1['key']->value())->toBe($pk1->value())
        ->and($row1['value'][0]->name())->toBe($attr1->name())
        ->and($row1['value'][0]->type())->toBe($attr1->type())
        ->and($row1['value'][0]->value())->toBe($attr1->value());

    $row2 = (new RowDecodableResponse($response->getTables()[0]->getRows()[1]))->getDecodedRow();
    expect($row2)->toBeArray()->toHaveKeys(['key', 'value'])
        ->and($row2['key']->name())->toBe($pk2->name())
        ->and($row2['key']->value())->toBe($pk2->value())
        ->and($row2['value'][0]->name())->toBe($attr2->name())
        ->and($row2['value'][0]->type())->toBe($attr2->type())
        ->and($row2['value'][0]->value())->toBe($attr2->value());
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('data retrieval with filter', function () {
    // prepare the testing data
    [$pk1, $attr1] = [PrimaryKey::string('key', 'filter-get-1'), Attribute::string('value', 'foo')];
    [$pk2, $attr2] = [PrimaryKey::string('key', 'filter-get-2'), Attribute::string('value', 'bar')];

    $response = tablestore()->batch(function ($builder) use ($pk1, $attr1, $pk2, $attr2) {
        $builder->table('rows')->insert([$pk1, $attr1]);
        $builder->table('rows')->insert([$pk2, $attr2]);
    });

    expect($response->getTables()->count())->toBe(1);

    $response = tablestore()->table('rows')
        ->where([$pk1])
        ->where($attr1->name(), '!=', $attr1->value())
        ->get();

    expect($response->getConsumed()->getCapacityUnit()->getRead())->toBe(1)
        ->and($response->getConsumed()->getCapacityUnit()->getWrite())->toBe(0)
        ->and($response->getDecodedRow())->toBeNull();
})->skip(! integrationTestEnabled(), 'integration test not enabled');
