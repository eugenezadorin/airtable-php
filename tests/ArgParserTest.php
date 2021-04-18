<?php

declare(strict_types=1);

use Zadorin\Airtable\Record;
use Zadorin\Airtable\ArgParser;
use Zadorin\Airtable\Errors;

it('properly converts mixed set of args', function () {
    $parsed = ArgParser::makeRecordsFromFields(
        ['key1' => 'value1'],
        new Record(['key2' => 'value2']),
        [
            ['key3' => 'value3'],
            ['key4' => 'value4']
        ],
        [
            new Record(['key5' => 'value5']),
            new Record(['key6' => 'value6'])
        ]
    );

    expect($parsed)->toBeArray();
    expect($parsed[0]->getFields())->toMatchArray(['key1' => 'value1']);
    expect($parsed[1]->getFields())->toMatchArray(['key2' => 'value2']);
    expect($parsed[2]->getFields())->toMatchArray(['key3' => 'value3']);
    expect($parsed[3]->getFields())->toMatchArray(['key4' => 'value4']);
    expect($parsed[4]->getFields())->toMatchArray(['key5' => 'value5']);
    expect($parsed[5]->getFields())->toMatchArray(['key6' => 'value6']);
});

it('allows only arrays and Record objects when creating records from fields', function () {
    ArgParser::makeRecordsFromFields(
        ['foo' => 'bar'],
        new Record(['foo' => 'bar']),
        'test'
    );
})->throws(Errors\InvalidArgument::class);

it('can create records using strings as record id', function () {
    $parsed = ArgParser::makeRecordsFromIds(
        'rec1',
        new Record([], 'rec2'),
        'rec3'
    );

    expect($parsed)->toBeArray();
    expect($parsed[0]->getId())->toEqual('rec1');
    expect($parsed[1]->getId())->toEqual('rec2');
    expect($parsed[2]->getId())->toEqual('rec3');
});

it('allows only strings and Record objects when creating records from ids', function () {
    ArgParser::makeRecordsFromIds(
        new Record(['foo' => 'bar'], 'rec1'),
        'rec2',
        100
    );
})->throws(Errors\InvalidArgument::class);

test('isArrayOfArrays() method works properly', function () {
    expect(ArgParser::isArrayOfArrays([
        'foo' => 'bar'
    ]))->toBeFalse();

    expect(ArgParser::isArrayOfArrays([
        'foo' => 'bar', 
        ['baz' => 'qux']
    ]))->toBeFalse();

    expect(ArgParser::isArrayOfArrays([
        ['foo' => 'bar'],
        'Qux'
    ]))->toBeFalse();

    expect(ArgParser::isArrayOfArrays([
        ['foo' => 'bar']
    ]))->toBeTrue();

    expect(ArgParser::isArrayOfArrays([
        ['foo' => 'bar'],
        ['baz' => 'qux']
    ]))->toBeTrue();
});
