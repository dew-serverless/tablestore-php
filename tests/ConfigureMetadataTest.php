<?php

use Dew\Tablestore\Middlewares\ConfigureMetadata;
use Dew\Tablestore\Tablestore;
use GuzzleHttp\Psr7\Request;

test('headers are sorted', function ($ots) {
    $apply = ConfigureMetadata::forOts($ots);
    $request = $apply(fn ($request, $options) => $request)(new Request('GET', '/'), []);
    $sorted = array_keys($request->getHeaders());
    sort($sorted);
    expect(array_keys($request->getHeaders()))->toBe($sorted);
})->with('tablestore instances');

test('header sts token is missing when not provided', function () {
    $ots = new Tablestore('key', 'secret', 'https://instance.cn-hangzhou.ots.aliyuncs.com');
    $apply = ConfigureMetadata::forOts($ots);
    $request = $apply(fn ($request, $options) => $request)(new Request('GET', '/'), []);
    expect($request->hasHeader('x-ots-ststoken'))->toBeFalse();
});

test('header has sts token when provided', function () {
    $ots = new Tablestore('key', 'secret', 'https://instance.cn-hangzhou.ots.aliyuncs.com');
    $ots->tokenUsing('token');
    $apply = ConfigureMetadata::forOts($ots);
    $request = $apply(fn ($request, $options) => $request)(new Request('GET', '/'), []);
    expect($request->hasHeader('x-ots-ststoken'))->toBeTrue()
        ->and($request->getHeaderLine('x-ots-ststoken'))->toBe('token');
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
