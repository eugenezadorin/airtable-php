<?php

declare(strict_types=1);

use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Request;

it('fails on empty uri', function () {
    $request = new Request();
    $request->setUri('');
})->throws(Errors\RequestError::class);

it('fails on unexpected request methods', function () {
    $request = new Request();
    $request->setMethod('HEAD');
})->throws(Errors\MethodNotAllowed::class);

it('can handle non-existent urls', function () {
    $request = new Request();
    $request->setUri('http://definitely-not-exists.tld');
    $result = $request->send();

    expect($result)->toBeEmpty();
    expect($request->isSuccess())->toBeFalse();
});

test('client keeps last request', function () {
    $client = client()->table('simple_selections');
    $client
        ->select('Name', 'Value')
        ->where(['Value' => 'Bar'])
        ->limit(2)
        ->execute();

    $request = $client->getLastRequest();

    expect($request)->toBeInstanceOf(Request::class);
    expect($request->isSuccess())->toBeTrue();
    expect($request->getPlainResponse())->toBeJson();
    expect($request->getResponseInfo())->toBeArray();
});
