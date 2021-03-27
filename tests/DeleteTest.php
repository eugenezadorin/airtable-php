<?php

declare(strict_types=1);

use Zadorin\Airtable\Errors\RecordsNotSpecified;
use Zadorin\Airtable\Errors\RequestError;
use Zadorin\Airtable\Record;

it('can remove single record', function () {
    $client = client()->table('removing');

    $inserted = $client
        ->insert(new Record(['timestamp' => 200]))
        ->execute()
        ->fetch();
    
    $recordToRemove = clone $inserted;

    $removed = $client
        ->delete($recordToRemove)
        ->execute()
        ->fetch();

    expect($inserted->getId())->toEqual($removed->getId());
    expect($removed->isDeleted())->toBeTrue();
});

it('can remove multiple records', function () {
    $client = client()->table('removing');

    $inserted = $client
        ->insert(new Record(['timestamp' => 300]), new Record(['timestamp' => 400]))
        ->execute()
        ->fetchAll();

    $insertedIds = array_map(fn($record) => $record->getId(), $inserted);

    $recordsToRemove = array_map(fn($recordId) => new Record([], $recordId), $insertedIds);

    $removed = $client
        ->delete(...$recordsToRemove)
        ->execute()
        ->fetchAll();

    $removedIds = array_map(fn($record) => $record->getId(), $removed);

    expect($removedIds)->toEqual($insertedIds);
});

it('throws error if no records specified', function() {
    $client = client()->table('removing');

    $removed = $client->delete()->execute();
})->throws(RecordsNotSpecified::class);

it('cannot remove record without specific record id', function() {
    $client = client()->table('removing');

    $removed = $client
        ->delete(new Record(['timestamp' => 100]))
        ->execute();
})->throws(RequestError::class);

it('can remove records by string ids', function () {
    $client = client()->table('removing');

    $inserted = $client
        ->insert(new Record(['timestamp' => 500]), new Record(['timestamp' => 600]))
        ->execute()
        ->fetchAll();

    $insertedIds = array_map(fn($record) => $record->getId(), $inserted);

    $removed = $client
        ->delete(...$insertedIds)
        ->execute()
        ->fetchAll();

    $removedIds = array_map(fn($record) => $record->getId(), $removed);

    expect($removedIds)->toEqual($insertedIds);
});