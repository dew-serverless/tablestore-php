<?php

use Dew\Tablestore\Middlewares\ConfigureMetadata;
use Dew\Tablestore\Tablestore;
use Dew\Tablestore\TablestoreInstance;
use GuzzleHttp\Psr7\Request;

test('ots headers are sorted', function ($ots) {
    $apply = ConfigureMetadata::forOts($ots);
    $request = $apply(fn ($request, $options) => $request)(new Request('GET', '/'), []);
    $sorted = array_keys($request->getHeaders());
    sort($sorted);
    expect(array_keys($request->getHeaders()))->toBe($sorted);
})->with('tablestore instances');

test('ots header sts token is missing when not provided', function () {
    $ots = new Tablestore('key', 'secret', 'https://instance.cn-hangzhou.ots.aliyuncs.com');
    $apply = ConfigureMetadata::forOts($ots);
    $request = $apply(fn ($request, $options) => $request)(new Request('GET', '/'), []);
    expect($request->hasHeader('x-ots-ststoken'))->toBeFalse();
});

test('ots header has sts token when provided', function () {
    $ots = new Tablestore('key', 'secret', 'https://instance.cn-hangzhou.ots.aliyuncs.com');
    $ots->tokenUsing('token');
    $apply = ConfigureMetadata::forOts($ots);
    $request = $apply(fn ($request, $options) => $request)(new Request('GET', '/'), []);
    expect($request->hasHeader('x-ots-ststoken'))->toBeTrue()
        ->and($request->getHeaderLine('x-ots-ststoken'))->toBe('token');
});

test('acs header token is missing when not provided', function () {
    $ots = new TablestoreInstance('key', 'secret', 'us-west-1');
    $applyMiddleware = ConfigureMetadata::forAcs($ots);
    $request = new Request('GET', '/');
    $options = ['acs' => ['action' => '', 'version' => '']];
    $handler = fn ($request, $options) => $request;
    $request = $applyMiddleware($handler)($request, $options);
    expect($request->hasheader('x-acs-security-token'))->toBeFalse();
});

test('acs header has token when provided', function () {
    $ots = new TablestoreInstance('key', 'secret', 'us-west-1');
    $ots->tokenUsing('token');
    $applyMiddleware = ConfigureMetadata::forAcs($ots);
    $request = new Request('GET', '/');
    $options = ['acs' => ['action' => '', 'version' => '']];
    $handler = fn ($request, $options) => $request;
    $request = $applyMiddleware($handler)($request, $options);
    expect($request->getHeaderLine('x-acs-accesskey-id'))->toBe('key')
        ->and($request->getHeaderLine('x-acs-security-token'))->toBe('token');
});

test('acs requires action', function () {
    $ots = new TablestoreInstance('key', 'secret', 'us-west-1');
    $applyMiddleware = ConfigureMetadata::forAcs($ots);
    $request = new Request('GET', '/');
    $options = ['acs' => ['version' => '']];
    $handler = fn ($request, $options) => $request;
    expect(fn () => $applyMiddleware($handler)($request, $options))
        ->toThrow(InvalidArgumentException::class, 'API requires action name.');
});

test('acs requires version', function () {
    $ots = new TablestoreInstance('key', 'secret', 'us-west-1');
    $applyMiddleware = ConfigureMetadata::forAcs($ots);
    $request = new Request('GET', '/');
    $options = ['acs' => ['action' => '']];
    $handler = fn ($request, $options) => $request;
    expect(fn () => $applyMiddleware($handler)($request, $options))
        ->toThrow(InvalidArgumentException::class, 'API requires version number.');
});

dataset('tablestore instances', [
    'regular' => [
        new Tablestore('key', 'secret', 'https://instance.cn-hangzhou.ots.aliyuncs.com'),
    ],
    'with sts token' => [
        (new Tablestore('key', 'secret', 'https://instance.cn-hangzhou.ots.aliyuncs.com'))
            ->tokenUsing('token'),
    ],
]);
