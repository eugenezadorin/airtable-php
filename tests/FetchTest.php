<?php

declare(strict_types=1);

use Zadorin\Airtable\Errors\RequestError;

it('can fetch simple data without any filtration', function () {
    $actual = client()->query()
        ->select('Name', 'Value')
        ->from('simple_selections')
        ->limit(2)
        ->execute();

    $expected = [
        ['Name' => 'Foo', 'Value' => 'Bar'],
        ['Name' => 'Baz', 'Value' => '123'],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('fetches all rows and columns if we not specify limits', function () {
    $actual = client()->query()
        ->select()
        ->from('simple_selections')
        ->execute();

    $expected = [
        ['Name' => 'Foo', 'Value' => 'Bar', 'Code' => 100],
        ['Name' => 'Baz', 'Value' => '123', 'Code' => 200],
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('allows to use * wildcard to select all fields', function () {
    $actual = client()->query()
        ->select('*')
        ->from('simple_selections')
        ->execute();

    $expected = [
        ['Name' => 'Foo', 'Value' => 'Bar', 'Code' => 100],
        ['Name' => 'Baz', 'Value' => '123', 'Code' => 200],
        ['Name' => 'Qux', 'Value' => 'Bar', 'Code' => 300],
    ];

    expect($actual->asArray())->toEqual($expected);
});

it('fails on unknown fields', function () {
    client()->query()
        ->select('Name', 'absolutely_unknown')
        ->from('simple_selections')
        ->execute();
})->throws(RequestError::class);

it('supports zero-limit', function () {
    $recordset = client()->query()
        ->select('Name', 'Value')
        ->from('simple_selections')
        ->where(['Value' => 'Bar'])
        ->limit(-1)
        ->execute();
    
    expect($recordset->isEmpty())->toBeTrue();
});
