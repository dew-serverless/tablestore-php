<?php

use Dew\Tablestore\Schema\Blueprint;
use Protos\ReservedThroughput;
use Protos\SSEKeyType;
use Protos\SSESpecification;

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
