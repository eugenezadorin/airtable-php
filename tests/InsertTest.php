<?php

declare(strict_types=1);

use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Record;

it('properly inserts single record', function () {
    $fields = [
        'name' => 'Ivan',
        'email' => 'ivan@test.tld',
    ];
    $record = new Record($fields);

    $recordset = client()->table('inserting')
        ->insert($record)
        ->execute();

    expect($recordset->count())->toEqual(1);
    expect($recordset->fetch()->getFields())->toMatchArray($fields);
});

it('properly inserts multiple records', function () {
    $fields1 = ['name' => 'Peter', 'email' => 'peter@test.tld'];
    $fields2 = ['name' => 'John', 'email' => 'john@test.tld'];

    $recordset = client()->table('inserting')
        ->insert(new Record($fields1), new Record($fields2))
        ->execute();

    expect($recordset->count())->toEqual(2);

    $added1 = $recordset->fetch()->getFields();
    $added2 = $recordset->fetch()->getFields();

    expect($added1)->toMatchArray($fields1);
    expect($added2)->toMatchArray($fields2);
    expect($added1['id'] + 1)->toEqual($added2['id']);
});

it('don\'t accept autoincrement fields', function () {
    $fields = [
        'id' => 2,
        'name' => 'Ivan',
        'email' => 'ivan@test.tld',
    ];
    $record = new Record($fields);

    client()->table('inserting')
        ->insert($record)
        ->execute();
})->throws(Errors\RequestError::class);

it('requires at least one record', function () {
    client()->table('inserting')
        ->insert()
        ->execute();
})->throws(Errors\RecordsNotSpecified::class);

it('can use array instead of record object', function () {
    $fields = [
        'name' => 'Sarah',
        'email' => 'sarah@test.tld',
    ];

    $recordset = client()->table('inserting')
        ->insert($fields)
        ->execute();

    expect($recordset->fetch()->getFields())->toMatchArray($fields);
});

it('can insert several records at once by passing an array of arrays', function () {
    $fields1 = ['name' => 'Jane', 'email' => 'jane@test.tld'];
    $fields2 = ['name' => 'Katty', 'email' => 'katty@test.tld'];

    $recordset = client()->table('inserting')
        ->insert([$fields1, $fields2])
        ->execute();

    $added1 = $recordset->fetch()->getFields();
    $added2 = $recordset->fetch()->getFields();

    expect($added1)->toMatchArray($fields1);
    expect($added2)->toMatchArray($fields2);
});
