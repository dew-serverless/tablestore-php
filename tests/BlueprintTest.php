<?php

use Dew\Tablestore\Schema\Blueprint;
use Protos\SSEKeyType;
use Protos\SSESpecification;

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
