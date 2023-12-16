<?php

test('list lists all instances', function () {
    $response = instance()->all();
    $data = json_decode($response->getBody()->getContents(), associative: true);
    expect($data)->toBeArray()->toHaveKeys(['TotalCount', 'NextToken', 'Instances']);
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('create creates new instance', function () {
    $response = instance()->create([
        'InstanceName' => $instance = 'test'.time(),
        'InstanceDescription' => 'bar',
        'AliasName' => 'foo',
        'ClusterType' => 'SSD',
        'Network' => 'NORMAL',
    ]);

    $data = json_decode($response->getBody()->getContents(), associative: true);
    expect($data)->toBeArray()->toHaveKey('RequestId');

    return $instance;
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('get retrieves instance information', function (string $instance) {
    $response = instance()->get($instance);
    $data = json_decode($response->getBody()->getContents(), associative: true);
    expect($data)->toBeArray()->toHaveKeys(['InstanceName', 'InstanceDescription', 'AliasName'])
        ->and($data['InstanceName'])->toBe($instance)
        ->and($data['InstanceDescription'])->toBe('bar')
        ->and($data['AliasName'])->toBe('foo');
})->depends('create creates new instance')
    ->skip(! integrationTestEnabled(), 'integration test not enabled');

test('update updates instance', function (string $instance) {
    $response = instance()->update([
        'InstanceName' => $instance,
        'InstanceDescription' => 'updated',
    ]);
    $data = json_decode($response->getBody()->getContents(), associative: true);
    expect($data)->toBeArray()->toHaveKey('RequestId');
})->depends('create creates new instance')
    ->skip(! integrationTestEnabled(), 'integration test not enabled');

test('tag attaches tags to instance', function (string $instance) {
    $response = instance()->tagInstance($instance, ['Test' => 'true']);
    $data = json_decode($response->getBody()->getContents(), associative: true);
    expect($data)->toBeArray()->toHaveKey('RequestId');

    $response = instance()->get($instance);
    $data = json_decode($response->getBody()->getContents(), associative: true);
    expect($data)->toBeArray()->toHaveKey('Tags')
        ->and($data['Tags'])->toBe([['Key' => 'Test', 'Value' => 'true']]);
})->depends('create creates new instance')
    ->skip(! integrationTestEnabled(), 'integration test not enabled');

test('untag removes tags from instance', function (string $instance) {
    $response = instance()->untagInstance($instance, 'Test');
    $data = json_decode($response->getBody()->getContents(), associative: true);
    expect($data)->toBeArray()->toHaveKey('RequestId');

    $response = instance()->get($instance);
    $data = json_decode($response->getBody()->getContents(), associative: true);
    expect($data)->toBeArray()->toHaveKey('Tags')
        ->and($data['Tags'])->toBe([]);
})->depends('create creates new instance')
    ->skip(! integrationTestEnabled(), 'integration test not enabled');

test('delete deletes the instance', function (string $instance) {
    $response = instance()->delete($instance);
    $data = json_decode($response->getBody()->getContents(), associative: true);
    expect($data)->toBeArray()->toHaveKey('RequestId');
})->depends('create creates new instance')
    ->skip(! integrationTestEnabled(), 'integration test not enabled');
