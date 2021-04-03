<?php

declare(strict_types=1);

use Zadorin\Airtable\Record;
use Zadorin\Airtable\Errors\RecordsNotSpecified;

it('can find multiple records by id', function () {
    $actual = client()->table('simple_selections')
        ->select('*')
        ->limit(2)
        ->execute();

    $recordIds = [];
    while ($record = $actual->fetch()) {
        $recordIds[] = $record->getId();
    }

    $expected = client()->table('simple_selections')->find(...$recordIds)->execute();

    expect($actual->asArray())->toEqual($expected->asArray());
});

it('fails when record id not specified', function () {
    $record = new Record();
    client()->table('simple_selections')->find($record)->execute();
})->throws(RecordsNotSpecified::class);
