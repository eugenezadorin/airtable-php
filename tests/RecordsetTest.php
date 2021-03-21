<?php

declare(strict_types=1);

use Zadorin\Airtable\Record;

it('can fetch records collection', function () {
    $recordset = client()->table('simple_selections')
        ->select('Name', 'Value')
        ->where(['Value' => 'Bar'])
        ->limit(2)
        ->execute();

    expect($recordset->count())->toEqual(2);

    $records = $recordset->fetchAll();
    
    expect($records[0])->toBeInstanceOf(Record::class);
    expect($records[0]->getFields()['Name'])->toEqual('Foo');

    expect($records[1])->toBeInstanceOf(Record::class);
    expect($records[1]->getFields()['Name'])->toEqual('Qux');
});

it('can iterate through records', function () {
    $recordset = client()->table('simple_selections')
        ->select('Name', 'Value')
        ->where(['Value' => 'Bar'])
        ->limit(2)
        ->execute();

    $actual = [];
    while ($record = $recordset->fetch()) {
        $actual[] = $record->getFields();
    }

    $expected = [
        ['Name' => 'Foo', 'Value' => 'Bar'],
        ['Name' => 'Qux', 'Value' => 'Bar'],
    ];

    expect($actual)->toEqual($expected);
});

it('properly works with empty recordset', function () {
    $recordset = client()->table('simple_selections')
        ->select('Name', 'Value')
        ->where(['Value' => 'Definitely Not Exists'])
        ->limit(2)
        ->execute();

    expect($recordset->isEmpty())->toBeTrue();
    expect($recordset->fetchAll())->toEqual([]);
    expect($recordset->fetch())->toBeNull();
});