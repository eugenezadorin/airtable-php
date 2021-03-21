<?php

declare(strict_types=1);

it('can filter records by single criteria', function () {
    $actual = client()->query()
        ->select('Name', 'Value')
        ->from('simple_selections')
        ->where(['Value' => 'Bar'])
        ->execute();

    $expected = [
        ['Name' => 'Foo', 'Value' => 'Bar'],
        ['Name' => 'Qux', 'Value' => 'Bar'],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('can filter records by multiple criteria', function () {
    $actual = client()->query()
        ->select('Name', 'Value')
        ->from('simple_selections')
        ->where(['Value' => 'Bar', 'Name' => 'Foo'])
        ->execute();

    $expected = [
        ['Name' => 'Foo', 'Value' => 'Bar'],
    ];

    expect($actual->asArray())->toEqual($expected);
});
