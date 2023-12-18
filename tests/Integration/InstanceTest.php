<?php

test('list lists all instances', function () {
    $response = instance()->all();
    expect($response->json())->toHaveKeys(['TotalCount', 'NextToken', 'Instances']);
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('create creates new instance', function () {
    $response = instance()->create([
        'InstanceName' => $instance = 'test'.time(),
        'InstanceDescription' => 'bar',
        'AliasName' => 'foo',
        'ClusterType' => 'SSD',
        'Network' => 'NORMAL',
    ]);

    expect($response->json('RequestId'))->toBeString()->not->toBeEmpty();

    return $instance;
})->skip(! integrationTestEnabled(), 'integration test not enabled');

test('get retrieves instance information', function (string $instance) {
    $response = instance()->get($instance);
    expect($response->json('InstanceName'))->toBe($instance)
        ->and($response->json('InstanceDescription'))->toBe('bar')
        ->and($response->json('AliasName'))->toBe('foo');
})->depends('create creates new instance')
    ->skip(! integrationTestEnabled(), 'integration test not enabled');

test('update updates instance', function (string $instance) {
    $response = instance()->update([
        'InstanceName' => $instance,
        'InstanceDescription' => 'updated',
    ]);
    expect($response->json('RequestId'))->toBeString()->not->toBeEmpty();
})->depends('create creates new instance')
    ->skip(! integrationTestEnabled(), 'integration test not enabled');

test('tag attaches tags to instance', function (string $instance) {
    $response = instance()->tagInstance($instance, ['Test' => 'true']);
    expect($response->json('RequestId'))->toBeString()->not->toBeEmpty();

    $response = instance()->get($instance);
    expect($response->json('Tags'))->toBe([['Key' => 'Test', 'Value' => 'true']]);
})->depends('create creates new instance')
    ->skip(! integrationTestEnabled(), 'integration test not enabled');

test('untag removes tags from instance', function (string $instance) {
    $response = instance()->untagInstance($instance, 'Test');
    expect($response->json('RequestId'))->toBeString()->not->toBeEmpty()
        ->and(instance()->get($instance)->json('Tags'))->toBe([]);
})->depends('create creates new instance')
    ->skip(! integrationTestEnabled(), 'integration test not enabled');

test('delete deletes the instance', function (string $instance) {
    $response = instance()->delete($instance);
    expect($response->json('RequestId'))->toBeString()->not->toBeEmpty();
})->depends('create creates new instance')
    ->skip(! integrationTestEnabled(), 'integration test not enabled');
