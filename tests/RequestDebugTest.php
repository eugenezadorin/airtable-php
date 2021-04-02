<?php

declare(strict_types=1);

use Zadorin\Airtable\Errors;
use Zadorin\Airtable\Request;

it('fails on empty uri', function () {
    $request = new Request();
    $request->setUri('');
})->throws(Errors\InvalidArgument::class);

it('fails on unexpected request methods', function () {
    $request = new Request();
    $request->setMethod('HEAD');
})->throws(Errors\MethodNotAllowed::class);

it('fails on non-existent urls', function () {
    $request = new Request();
    $request->setUri('http://definitely-not-exists.tld');
    $request->send();
})->throws(Errors\RequestError::class);

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

test('request exception contains latest request', function () {
    $request = null;
    try {
        $request = new Request();
        $request->setUri('http://definitely-not-exists.tld');
        $request->send();
    } catch (Errors\RequestError $e) {
        $request = $e->getLastRequest();
    }

    expect($request)->not->toBeNull();
    expect($request)->toBeInstanceOf(Request::class);
});

it('provides different levels of exceptions', function () {
    $airtableError = false;
    try {
        client()->table('inserting')->insert()->execute();
    } catch (Errors\RequestError $e) {
        $airtableError = false;
    } catch (Errors\AirtableError $e) {
        $airtableError = true;
    }

    expect($airtableError)->toBeTrue();
});
