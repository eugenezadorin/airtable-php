<?php

declare(strict_types=1);

use Zadorin\Airtable\Record;

it('properly updates single record by record id', function () {
    $client = client()->table('updating');

    $recordset = $client->insert(new Record(['name' => 'Peter']))->execute();

    $inserted = $recordset->fetch();
    
    $update = clone $inserted;
    $update->setFields(['name' => 'Ivan']);

    $recordset = $client->update($update)->execute();

    expect($recordset->count())->toEqual(1);
    expect($recordset->fetch()->getFields())->toMatchArray(['name' => 'Ivan']);

    $recordset = $client
        ->select()
        ->where(['id' => $inserted->getFields()['id']])
        ->execute();

    expect($recordset->count())->toEqual(1);
    expect($recordset->fetch()->getFields())->toMatchArray(['name' => 'Ivan']);
});

it('properly updates multiple records', function () {
    $client = client()->table('updating');

    $recordset = $client->insert(
        new Record(['name' => 'Kate']),
        new Record(['name' => 'Mary'])
    )->execute();

    $inserted = $recordset->fetchAll();

    $inserted[0]->setFields(['name' => 'Ashley']);
    $inserted[1]->setFields(['name' => 'Ann']);

    $recordset = $client->update(...$inserted)->execute();
    expect($recordset->count())->toEqual(2);
    
    $actual = $recordset->asArray();
    foreach ($actual as $key => $value) {
        expect($value)->toMatchArray($inserted[$key]->getFields());
    }
});
