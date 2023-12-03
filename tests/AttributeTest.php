<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\Cells\BooleanAttribute;
use Dew\Tablestore\Cells\DoubleAttribute;
use Dew\Tablestore\Cells\IntegerAttribute;
use Dew\Tablestore\Cells\StringAttribute;

test('boolean attribute resolution', function () {
    $attribute = Attribute::createFromValue('value', true);
    expect($attribute)->toBeInstanceOf(BooleanAttribute::class);

    $attribute = Attribute::createFromValue('value', false);
    expect($attribute)->toBeInstanceOf(BooleanAttribute::class);
});

test('double attribute resolution', function () {
    $attribute = Attribute::createFromValue('value', 3.14);
    expect($attribute)->toBeInstanceOf(DoubleAttribute::class);
});

test('integer attribute resolution', function () {
    $attribute = Attribute::createFromValue('value', 100);
    expect($attribute)->toBeInstanceOf(IntegerAttribute::class);
});

test('string attribute resolution', function () {
    $attribute = Attribute::createFromValue('value', 'foo');
    expect($attribute)->toBeInstanceOf(StringAttribute::class);
});

test('unsupported attribute type resolution', function () {
    expect(fn () => Attribute::createFromValue('value', null))
        ->toThrow(InvalidArgumentException::class, 'Could not build an attribute from the [NULL] type.');
});
