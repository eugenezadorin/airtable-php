<?php

declare(strict_types=1);

use Zadorin\Airtable\Errors;

test('execute() method returns first page only', function () {
    $recordset = client()->table('pagination')
        ->select('id')
        ->paginate(50)
        ->execute();

    expect($recordset->count())->toEqual(50);
});

it('provides simple pagination', function () {
    $query = client()->table('pagination')
        ->select('id')
        ->orderBy(['id' => 'asc'])
        ->paginate(50);

    $page1 = $query->nextPage();
    $page2 = $query->nextPage();
    $page3 = $query->nextPage();
    $page4 = $query->nextPage();

    expect($page1->count())->toEqual(50);
    expect($page2->count())->toEqual(50);
    expect($page3->count())->toEqual(20);
    expect($page4)->toBeNull();

    expect($page1->fetch()->getFields())->toMatchArray(['id' => 1]);
    expect($page2->fetch()->getFields())->toMatchArray(['id' => 51]);
    expect($page3->fetch()->getFields())->toMatchArray(['id' => 101]);
});

it('fails on big page sizes', function () {
    client()->table('pagination')
        ->select('id')
        ->orderBy(['id' => 'asc'])
        ->paginate(120);
})->throws(Errors\PageSizeTooLarge::class);
