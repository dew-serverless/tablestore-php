<?php

use Dew\Tablestore\Tablestore;

test('request options default timeout is 2.0', function () {
    $ots = new Tablestore('key', 'secret', 'https://instance.cn-hangzhou.ots.aliyuncs.com');
    expect($ots->options()['timeout'])->toBe(2.0);
});

test('request options configure', function () {
    $ots = new Tablestore('key', 'secret', 'https://instance.cn-hangzhou.ots.aliyuncs.com');
    $ots->optionsUsing(['allow_redirects' => false]);
    expect($ots->options()['allow_redirects'])->toBeFalse();
});
