<?php

use Dew\Tablestore\GuessInstanceName;

test('instance resolution public endpoint', function () {
    expect(GuessInstanceName::make('https://instance.cn-hangzhou.ots.aliyuncs.com'))
        ->toBe('instance');
});

test('instance resolution vpc endpoint', function () {
    expect(GuessInstanceName::make('https://instance.cn-hangzhou.vpc.tablestore.aliyuncs.com'))
        ->toBe('instance');
});

test('resolution with invalid endpoint', function () {
    expect(fn () => GuessInstanceName::make(''))
        ->toThrow(InvalidArgumentException::class, 'Could not resolve the instance from the endpoint [].');
});
