<?php

use Dew\Tablestore\Attribute;
use Dew\Tablestore\Builder;
use Dew\Tablestore\Cells\StringAttribute;
use Dew\Tablestore\PrimaryKey;
use Protos\Filter;

test('where key filters primary keys', function () {
    $builder = new Builder;
    $builder->whereKey($key = PrimaryKey::string('key', 'foo'));
    expect($builder->whereKeys)->toBe([$key]);
});

test('where key accepts multiple keys', function () {
    $builder = new Builder;
    $builder->whereKey([$key1 = PrimaryKey::string('key1', 'foo'), $key2 = PrimaryKey::string('key2', 'bar')]);
    expect($builder->whereKeys)->toBe([$key1, $key2]);
});

test('where column constructs condition', function () {
    $builder = new Builder;
    $builder->whereColumn('name', 'Zhineng');
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['comparison'])->toBe('=')
        ->and($builder->wheres[0]['column'])->toBeInstanceOf(StringAttribute::class)
        ->and($builder->wheres[0]['column']->name())->toBe('name')
        ->and($builder->wheres[0]['column']->value())->toBe('Zhineng')
        ->and($builder->wheres[0]['logical'])->toBe('and');
});

test('where column constructs condition with comparison operator', function () {
    $builder = new Builder;
    $builder->whereColumn('name', '!=', 'Zhineng');
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['comparison'])->toBe('!=')
        ->and($builder->wheres[0]['column'])->toBeInstanceOf(StringAttribute::class)
        ->and($builder->wheres[0]['column']->name())->toBe('name')
        ->and($builder->wheres[0]['column']->value())->toBe('Zhineng')
        ->and($builder->wheres[0]['logical'])->toBe('and');
});

test('where column constructs condition with logical operator', function () {
    $builder = new Builder;
    $builder->whereColumn('name', '!=', 'Zhineng', 'or');
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['comparison'])->toBe('!=')
        ->and($builder->wheres[0]['column'])->toBeInstanceOf(StringAttribute::class)
        ->and($builder->wheres[0]['column']->name())->toBe('name')
        ->and($builder->wheres[0]['column']->value())->toBe('Zhineng')
        ->and($builder->wheres[0]['logical'])->toBe('or');
});

test('where column constructs condition with attribute', function () {
    $attribute = Attribute::string('name', 'Zhineng');
    $builder = new Builder;
    $builder->whereColumn($attribute);
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['comparison'])->toBe('=')
        ->and($builder->wheres[0]['column'])->toBeInstanceOf($attribute::class)
        ->and($builder->wheres[0]['column']->name())->toBe($attribute->name())
        ->and($builder->wheres[0]['column']->value())->toBe($attribute->value())
        ->and($builder->wheres[0]['logical'])->toBe('and');
});

test('where column constructs multiple conditions', function ($attributes) {
    $builder = new Builder;
    $builder->whereColumn($attributes);
    expect($builder->wheres)->toHaveCount(2)
        ->and($builder->wheres[0]['comparison'])->toBe('=')
        ->and($builder->wheres[0]['logical'])->toBe('and')
        ->and($builder->wheres[1]['comparison'])->toBe('=')
        ->and($builder->wheres[1]['logical'])->toBe('and');
})->with('multiple attributes');

test('where column comparison operator must be a string', function () {
    $builder = new Builder;
    expect(fn () => $builder->where('name', null, 'Zhineng'))
        ->toThrow(InvalidArgumentException::class, 'Comparison operator accepts =, !=, <>, >, >=, <, or <=.');
});

test('where column could not build with unsupported value type', function () {
    $builder = new Builder;
    expect(fn () => $builder->whereColumn('name', null))
        ->toThrow(InvalidArgumentException::class, 'Could not build an attribute from the [NULL] type.');
});

test('where not column constructs negation statement', function () {
    $builder = new Builder;
    $builder->whereNotColumn('name', 'Zhineng');
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['comparison'])->toBe('=')
        ->and($builder->wheres[0]['column'])->toBeInstanceOf(StringAttribute::class)
        ->and($builder->wheres[0]['column']->name())->toBe('name')
        ->and($builder->wheres[0]['column']->value())->toBe('Zhineng')
        ->and($builder->wheres[0]['logical'])->toBe('and')
        ->and($builder->wheres[0]['negative'])->toBeTrue();
});

test('where not column constructs negation statement with attribute', function () {
    $attribute = Attribute::string('name', 'Zhineng');
    $builder = new Builder;
    $builder->whereNotColumn($attribute);
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['comparison'])->toBe('=')
        ->and($builder->wheres[0]['column'])->toBeInstanceOf(StringAttribute::class)
        ->and($builder->wheres[0]['column']->name())->toBe($attribute->name())
        ->and($builder->wheres[0]['column']->value())->toBe($attribute->value())
        ->and($builder->wheres[0]['logical'])->toBe('and')
        ->and($builder->wheres[0]['negative'])->toBeTrue();
});

test('where not column constructs multiple negation statements', function ($attributes) {
    $builder = new Builder;
    $builder->whereNotColumn($attributes);
    expect($builder->wheres)->toHaveCount(2)
        ->and($builder->wheres[0]['comparison'])->toBe('=')
        ->and($builder->wheres[0]['logical'])->toBe('and')
        ->and($builder->wheres[0]['negative'])->toBeTrue()
        ->and($builder->wheres[1]['comparison'])->toBe('=')
        ->and($builder->wheres[1]['logical'])->toBe('and')
        ->and($builder->wheres[1]['negative'])->toBeTrue();
})->with('multiple attributes');

test('or where column constructs logical-or condition', function () {
    $builder = new Builder;
    $builder->orWhereColumn('name', '!=', 'Zhineng');
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['comparison'])->toBe('!=')
        ->and($builder->wheres[0]['column'])->toBeInstanceOf(StringAttribute::class)
        ->and($builder->wheres[0]['column']->name())->toBe('name')
        ->and($builder->wheres[0]['column']->value())->toBe('Zhineng')
        ->and($builder->wheres[0]['logical'])->toBe('or');
});

test('or where column constructs logical-or condition with attribute', function () {
    $attribute = Attribute::string('name', 'Zhineng');
    $builder = new Builder;
    $builder->orWhereColumn($attribute);
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['comparison'])->toBe('=')
        ->and($builder->wheres[0]['logical'])->toBe('or');
});

test('or where column constructs multiple logical-or conditions', function ($attributes) {
    $builder = new Builder;
    $builder->orWhereColumn($attributes);
    expect($builder->wheres)->toHaveCount(2)
        ->and($builder->wheres[0]['comparison'])->toBe('=')
        ->and($builder->wheres[0]['logical'])->toBe('or')
        ->and($builder->wheres[1]['comparison'])->toBe('=')
        ->and($builder->wheres[1]['logical'])->toBe('or');
})->with('multiple attributes');

test('or where not column constructs negative logical-or condition', function () {
    $builder = new Builder;
    $builder->orWhereNotColumn('name', 'Zhineng');
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['comparison'])->toBe('=')
        ->and($builder->wheres[0]['column'])->toBeInstanceOf(StringAttribute::class)
        ->and($builder->wheres[0]['column']->name())->toBe('name')
        ->and($builder->wheres[0]['column']->value())->toBe('Zhineng')
        ->and($builder->wheres[0]['logical'])->toBe('or')
        ->and($builder->wheres[0]['negative'])->toBeTrue();
});

test('or where not column constructs negative logical-or condition with attribute', function () {
    $attribute = Attribute::string('name', 'Zhineng');
    $builder = new Builder;
    $builder->orWhereNotColumn($attribute);
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['comparison'])->toBe('=')
        ->and($builder->wheres[0]['column'])->toBeInstanceOf(StringAttribute::class)
        ->and($builder->wheres[0]['column']->name())->toBe($attribute->name())
        ->and($builder->wheres[0]['column']->value())->toBe($attribute->value())
        ->and($builder->wheres[0]['logical'])->toBe('or')
        ->and($builder->wheres[0]['negative'])->toBeTrue();
});

test('or where not column constructs multiple negative logical-or conditions', function ($attributes) {
    $builder = new Builder;
    $builder->orWhereNotColumn($attributes);
    expect($builder->wheres)->toHaveCount(2)
        ->and($builder->wheres[0]['comparison'])->toBe('=')
        ->and($builder->wheres[0]['logical'])->toBe('or')
        ->and($builder->wheres[0]['negative'])->toBeTrue()
        ->and($builder->wheres[1]['comparison'])->toBe('=')
        ->and($builder->wheres[1]['logical'])->toBe('or')
        ->and($builder->wheres[1]['negative'])->toBeTrue();
})->with('multiple attributes');

test('where column grouping', function () {
    $builder = new Builder;
    $builder->whereColumn(function ($builder) {
        $builder->whereColumn('attr1', 'foo')->whereColumn('attr2', 'bar');
    });
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['logical'])->toBe('and')
        ->and($builder->wheres[0]['column'])->toBeArray()
        ->and($builder->wheres[0]['column'])->toHaveCount(2)
        ->and($builder->wheres[0]['negative'])->toBeFalse();
});

test('or where column grouping', function () {
    $builder = new Builder;
    $builder->orWhereColumn(function ($builder) {
        $builder->whereColumn('attr1', 'foo')->whereColumn('attr2', 'bar');
    });
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['logical'])->toBe('or')
        ->and($builder->wheres[0]['column'])->toBeArray()
        ->and($builder->wheres[0]['column'])->toHaveCount(2)
        ->and($builder->wheres[0]['negative'])->toBeFalse();
});

test('where not column grouping', function () {
    $builder = new Builder;
    $builder->whereNotColumn(function ($builder) {
        $builder->whereColumn('attr1', 'foo')->whereColumn('attr2', 'bar');
    });
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['logical'])->toBe('and')
        ->and($builder->wheres[0]['column'])->toBeArray()
        ->and($builder->wheres[0]['column'])->toHaveCount(2)
        ->and($builder->wheres[0]['negative'])->toBeTrue();
});

test('or where not column grouping', function () {
    $builder = new Builder;
    $builder->orWhereNotColumn(function ($builder) {
        $builder->whereColumn('attr1', 'foo')->whereColumn('attr2', 'bar');
    });
    expect($builder->wheres)->toHaveCount(1)
        ->and($builder->wheres[0]['logical'])->toBe('or')
        ->and($builder->wheres[0]['column'])->toBeArray()
        ->and($builder->wheres[0]['column'])->toHaveCount(2)
        ->and($builder->wheres[0]['negative'])->toBeTrue();
});

test('where filter accepts filter message', function () {
    $builder = new Builder;
    $builder->whereFilter($filter = new Filter);
    expect($builder->filter)->toBe($filter);
});

test('where accepts primary key', function () {
    $builder = new Builder;
    $builder->where($key = PrimaryKey::string('key', 'foo'));
    expect($builder->whereKeys)->toBe([$key]);
});

test('where accepts multiple primary keys', function () {
    $builder = new Builder;
    $builder->where([$key1 = PrimaryKey::string('key1', 'foo'), $key2 = PrimaryKey::string('key2', 'bar')]);
    expect($builder->whereKeys)->toBe([$key1, $key2]);
});

test('where accepts attribute', function ($api) {
    $attribute = Attribute::string('name', 'Zhineng');
    $builder = new Builder;
    $builder->$api($attribute);
    expect($builder->wheres)->toHaveCount(1);
})->with('super where apis');

test('where accepts multiple attributes', function ($api, $attributes) {
    $builder = new Builder;
    $builder->$api($attributes);
    expect($builder->wheres)->toHaveCount(2);
})->with('super where apis', 'multiple attributes');

test('where accepts column grouping', function ($api) {
    $builder = new Builder;
    $builder->$api(fn ($builder) => $builder->where('attr2', 'bar')->where('attr3', 'baz'));
    expect($builder->wheres)->toHaveCount(1);
})->with('super where apis');

test('where accepts filter message', function () {
    $builder = new Builder;
    $builder->where($filter = new Filter);
    expect($builder->filter)->toBe($filter);
});

dataset('multiple attributes', [
    'name and values' => [[
        ['attr1', 'foo'],
        ['attr2', 'bar'],
    ]],
    'name, comparison operator and values' => [[
        ['attr1', '=', 'foo'],
        ['attr2', '=', 'bar'],
    ]],
    'attributes' => [[
        Attribute::string('attr1', 'foo'),
        Attribute::string('attr2', 'bar'),
    ]],
]);

dataset('super where apis', [
    'where',
    'orWhere',
    'whereNot',
    'orWhereNot',
]);
