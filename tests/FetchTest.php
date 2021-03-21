<?php

declare(strict_types=1);

use Zadorin\Airtable\Errors\RequestError;

it('can fetch simple data without any filtration', function () {
    $actual = client()->table('simple_selections')
        ->select('Name', 'Value')
        ->limit(2)
        ->execute();

    $expected = [
        ['Name' => 'Foo', 'Value' => 'Bar'],
        ['Name' => 'Baz', 'Value' => '123'],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('fetches all rows and columns if we not specify limits', function () {
    $actual = client()->table('simple_selections')
        ->select()
        ->execute();

    $expected = [
        ['Name' => 'Foo', 'Value' => 'Bar', 'Code' => 100],
        ['Name' => 'Baz', 'Value' => '123', 'Code' => 200],
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('allows to use * wildcard to select all fields', function () {
    $actual = client()->table('simple_selections')
        ->select('*')
        ->execute();

    $expected = [
        ['Name' => 'Foo', 'Value' => 'Bar', 'Code' => 100],
        ['Name' => 'Baz', 'Value' => '123', 'Code' => 200],
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('fails on unknown fields', function () {
    client()->table('simple_selections')
        ->select('Name', 'absolutely_unknown')
        ->execute();
})->throws(RequestError::class);

it('supports zero-limit', function () {
    $recordset = client()->table('simple_selections')
        ->select('Name', 'Value')
        ->where(['Value' => 'Bar'])
        ->limit(-1)
        ->execute();
    
    expect($recordset->isEmpty())->toBeTrue();
});
