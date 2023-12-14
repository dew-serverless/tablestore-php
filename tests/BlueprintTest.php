<?php

use Dew\Tablestore\Schema\Blueprint;
use Protos\ReservedThroughput;
use Protos\SSEKeyType;
use Protos\SSESpecification;
use Protos\TableOptions;

test('throughput reserves read', function () {
    $table = (new Blueprint)->reserveRead(2);
    expect($table->throughput)->toBeInstanceOf(ReservedThroughput::class)
        ->and($table->throughput->getCapacityUnit()->getRead())->toBe(2)
        ->and($table->throughput->getCapacityUnit()->hasWrite())->toBeFalse();
});

test('throughput reserves write', function () {
    $table = (new Blueprint)->reserveWrite(1);
    expect($table->throughput)->toBeInstanceOf(ReservedThroughput::class)
        ->and($table->throughput->getCapacityUnit()->getWrite())->toBe(1)
        ->and($table->throughput->getCapacityUnit()->hasRead())->toBeFalse();
});

test('throughput reservations', function () {
    $table = (new Blueprint)->reserveRead(2)->reserveWrite(1);
    expect($table->throughput)->toBeInstanceOf(ReservedThroughput::class)
        ->and($table->throughput->getCapacityUnit()->getRead())->toBe(2)
        ->and($table->throughput->getCapacityUnit()->getWrite())->toBe(1);
});

test('table option configures time-to-live', function () {
    $table = (new Blueprint)->ttl(86400);
    expect($table->options)->toBeInstanceOf(TableOptions::class)
        ->and($table->options->getTimeToLive())->toBe(86400);
});

test('table option configures data that is stored permanently', function () {
    $table = (new Blueprint)->forever();
    expect($table->options)->toBeInstanceOf(TableOptions::class)
        ->and($table->options->getTimeToLive())->toBe(-1);
});

test('table option defines max versions to persist', function () {
    $table = (new Blueprint)->maxVersions(2);
    expect($table->options)->toBeInstanceOf(TableOptions::class)
        ->and($table->options->getMaxVersions())->toBe(2);
});

test('table option limits version offset', function () {
    $table = (new Blueprint)->versionOffsetIn(86400 * 2); // 2 days
    expect($table->options)->toBeInstanceOf(TableOptions::class)
        ->and($table->options->getDeviationCellVersionInSec())->toBe(86400 * 2);
});

test('table option allows update', function () {
    $table = (new Blueprint)->allowUpdate();
    expect($table->options)->toBeInstanceOf(TableOptions::class)
        ->and($table->options->getAllowUpdate())->toBeTrue();
});

test('table option denies update', function () {
    $table = (new Blueprint)->allowUpdate(false);
    expect($table->options)->toBeInstanceOf(TableOptions::class)
        ->and($table->options->getAllowUpdate())->toBeFalse();
});

test('sse encrypts with kms', function () {
    $table = (new Blueprint)->encryptWithKms();
    $sse = $table->encryption;
    expect($sse)->toBeInstanceOf(SSESpecification::class)
        ->and($sse->getEnable())->toBeTrue()
        ->and($sse->getKeyType())->toBe(SSEKeyType::SSE_KMS_SERVICE);
});

test('sse encrypts with own key', function () {
    $table = (new Blueprint)->encryptWith('key-id', 'role-arn');
    $sse = $table->encryption;
    expect($sse)->toBeInstanceOf(SSESpecification::class)
        ->and($sse->getEnable())->toBeTrue()
        ->and($sse->getKeyType())->toBe(SSEKeyType::SSE_BYOK)
        ->and($sse->getKeyId())->toBe('key-id')
        ->and($sse->getRoleArn())->toBe('role-arn');
});

test('data is not encrypted by default', function () {
    $table = new Blueprint;
    expect($table->encryption)->toBeNull();
});
