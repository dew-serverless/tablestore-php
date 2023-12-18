<?php

use Dew\Tablestore\Support\Arr;

test('get gets with key', function () {
    $data = ['foo' => 'bar'];
    expect(Arr::get($data, 'foo'))->toBe('bar');
});

test('get gets with default value', function () {
    $data = ['foo' => 'bar'];
    expect(Arr::get($data, 'name'))->toBeNull();
    expect(Arr::get($data, 'name', 'Zhineng'))->toBe('Zhineng');
});

test('get gets with dotted notation', function () {
    $data = ['nested' => ['foo' => 'bar']];
    expect(Arr::get($data, 'nested.foo'))->toBe('bar');
    expect(Arr::get($data, 'nested.foo.bar'))->toBeNull();
});

test('get gets with key contains a dot', function () {
    $data = ['nested.foo' => 'bar', 'nested' => ['foo' => 'baz']];
    expect(Arr::get($data, 'nested.foo'))->toBe('bar');
});

test('get gets with index key', function () {
    $data = ['foo', 'bar'];
    expect(Arr::get($data, 0))->toBe('foo');
    expect(Arr::get($data, 1))->toBe('bar');
    expect(Arr::get($data, 2, 'baz'))->toBe('baz');
});

test('get gets with nested index key', function () {
    $data = ['list' => ['foo', 'bar']];
    expect(Arr::get($data, 'list.0'))->toBe('foo');
    expect(Arr::get($data, 'list.1'))->toBe('bar');
    expect(Arr::get($data, 'list.2', 'baz'))->toBe('baz');
});
