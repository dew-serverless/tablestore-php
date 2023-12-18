<?php

use Dew\Tablestore\Responses\Response;
use GuzzleHttp\Psr7\Response as Psr7Response;

test('json decodes response body', function () {
    $response = new Response(new Psr7Response(body: json_encode(['foo' => 'bar'])));
    expect($response->json())->toBe(['foo' => 'bar']);
});

test('json retrieves with dotted notation', function () {
    $response = new Response(new Psr7Response(body: json_encode(['foo' => 'bar'])));
    expect($response->json('foo'))->toBe('bar');
});

test('json retrieves with default value', function () {
    $response = new Response(new Psr7Response(body: json_encode(['foo' => 'bar'])));
    expect($response->json('name', 'Zhineng'))->toBe('Zhineng');
});

test('json decodes with empty data', function () {
    $response = new Response(new Psr7Response);
    expect($response->json())->toBe([]);
});

test('json decodes with non-json data', function () {
    $response = new Response(new Psr7Response(body: '1'));
    expect(fn () => $response->json())
        ->toThrow(RuntimeException::class, 'Failed to decode the content [1] as JSON data.');
});

test('method redirection to psr7 response', function () {
    $response = new Response(new Psr7Response(body: json_encode(['foo' => 'bar'])));
    expect($response->getStatusCode())->toBe(200);
});
