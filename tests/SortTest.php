<?php

declare(strict_types=1);

it('can sort by one field', function () {
    $actual = client()->table('sorting')
        ->select()
        ->orderBy(['col1' => 'desc'])
        ->execute();

    $expected = [
        ['col1' => 300, 'col2' => 400, 'col3' => 'qwe'],
        ['col1' => 200, 'col2' => 500, 'col3' => 'def'],
        ['col1' => 100, 'col2' => 400, 'col3' => 'abc'],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('can sort by multiple fields', function () {
    $actual = client()->table('sorting')
        ->select()
        ->orderBy(['col2' => 'asc', 'col3' => 'desc'])
        ->execute();

    $expected = [
        ['col1' => 300, 'col2' => 400, 'col3' => 'qwe'],
        ['col1' => 100, 'col2' => 400, 'col3' => 'abc'],
        ['col1' => 200, 'col2' => 500, 'col3' => 'def'],
    ];

    expect($actual->asArray())->toEqual($expected);
});