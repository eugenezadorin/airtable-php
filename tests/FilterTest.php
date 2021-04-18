<?php

declare(strict_types=1);

it('can filter records by single criteria', function () {
    $actual = client()->table('simple_selections')
        ->select('Name', 'Value')
        ->where(['Value' => 'Bar'])
        ->execute();

    $expected = [
        ['Name' => 'Foo', 'Value' => 'Bar'],
        ['Name' => 'Qux', 'Value' => 'Bar'],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('can filter records by multiple criteria', function () {
    $actual = client()->table('simple_selections')
        ->select('Name', 'Value')
        ->where(['Value' => 'Bar', 'Name' => 'Foo'])
        ->execute();

    $expected = [
        ['Name' => 'Foo', 'Value' => 'Bar'],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('can concat multiple where statements', function () {
    $actual = client()->table('simple_selections')
        ->select('Name', 'Value', 'Code')
        ->where('Value', 'Bar')
        ->andWhere('Name', 'Qux')
        ->execute();

    $expected = [
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('can filter by not-equal criteria', function () {
    $actual = client()->table('simple_selections')
        ->select('Name', 'Value', 'Code')
        ->where([
            ['Value', '=', 'Bar'],
            ['Name', '!=', 'Foo']
        ])->execute();

    $expected = [
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('can filter by more-less criteria', function () {
    $actual = client()->table('simple_selections')
        ->select('Name', 'Value', 'Code')
        ->where([
            ['Code', '>', 100],
            ['Code', '<', 300]
        ])->execute();

    $expected = [
        ['Name' => 'Baz', 'Value' => '123', 'Code' => 200],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('can use OR statement', function () {
    $query = client()->table('simple_selections')
        ->select('*')
        ->where('Code', '>', 100)
        ->andWhere('Code', '<', 300)
        ->orWhere('Name', 'Qux');

    $formula = $query->getFormula();
    $result = $query->execute();

    expect($formula)->toEqual("OR(AND({Code}>'100', {Code}<'300'), {Name}='Qux')");
    expect($result->asArray())->toEqual([
        ['Name' => 'Baz', 'Value' => '123', 'Code' => 200],
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ]);
});

it('can parse nested statements', function () {
    $query = client()->table('simple_selections')
        ->select('*')
        ->where([
            ['Code', '>', 100],
            ['Code', '<', 300]
        ])
        ->orWhere('Name', 'Qux');
    
    $formula = $query->getFormula();
    $result = $query->execute();

    expect($formula)->toEqual("OR(AND({Code}>'100', {Code}<'300'), {Name}='Qux')");
    expect($result->asArray())->toEqual([
        ['Name' => 'Baz', 'Value' => '123', 'Code' => 200],
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ]);
});

it('uses AND logic inside OR statement', function () {
    $query = client()->table('simple_selections')
        ->select('Name', 'Value', 'Code')
        ->where('Code', '>', 200)
        ->orWhere([
            ['Code', '<=', 200],
            ['Name', '=', 'Foo'],
        ]);

    $formula = $query->getFormula();
    $result = $query->execute();

    expect($formula)->toEqual("OR({Code}>'200', AND({Code}<='200', {Name}='Foo'))");

    expect($result->asArray())->toEqual([
        ['Name' => 'Foo', 'Value' => 'Bar', 'Code' => 100],
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ]);
});

it('can use multiple OR statements', function () {
    $query = client()->table('simple_selections')
        ->select('Name', 'Value', 'Code')
        ->where('Code', '>', 200)
        ->orWhere('Name', '=', 'Foo')
        ->orWhere('Value', '=', '123');

    $formula = $query->getFormula();
    $result = $query->execute();

    expect($formula)->toEqual("OR(OR({Code}>'200', {Name}='Foo'), {Value}='123')");

    expect($result->asArray())->toEqual([
        ['Name' => 'Foo', 'Value' => 'Bar', 'Code' => 100],
        ['Name' => 'Baz', 'Value' => '123', 'Code' => 200],
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ]);
});

it('properly resolves andWhere after orWhere', function () {
    $query = client()->table('simple_selections')
        ->select('Name', 'Value', 'Code')
        ->where('Value', '=', 'Bar')
        ->orWhere('Value', '=', '123')
        ->andWhere('Code', '>=', 200);

    $formula = $query->getFormula();
    $result = $query->execute();

    expect($formula)->toEqual("OR({Value}='Bar', AND({Value}='123', {Code}>='200'))");

    expect($result->asArray())->toEqual([
        ['Name' => 'Foo', 'Value' => 'Bar', 'Code' => 100],
        ['Name' => 'Baz', 'Value' => '123', 'Code' => 200],
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ]);
});

it('can filter by raw formula', function () {
    $actual = client()->table('simple_selections')
        ->select('Name', 'Value', 'Code')
        ->whereRaw("OR( AND({Code}>'100', {Code}<'300'), {Name}='Qux' )")
        ->execute();

    $expected = [
        ['Name' => 'Baz', 'Value' => '123', 'Code' => 200],
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ];

    expect($actual->asArray())->toEqual($expected);
});
