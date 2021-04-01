# Airtable PHP client

## Installation

    composer require zadorin/airtable-php

## Usage

```php
$apiKey = 'key***********';
$database = 'app***********';
$tableName = 'my-table';

$client = new \Zadorin\Airtable\Client($apiKey, $database);

// insert some rows
$client->table($tableName)
    ->insert([
        ['name' => 'Ivan', 'email' => 'ivan@test.tld'],
        ['name' => 'Peter', 'email' => 'peter@test.tld']
    ])
    ->execute();

// fetch data
$recordset = $client->table($tableName)
    ->select('id', 'name', 'email')
    ->where(['name' => 'Ivan', 'email' => 'ivan@test.tld'])
    ->orderBy(['id' => 'desc'])
    ->limit(10)
    ->execute();

var_dump($recordset->fetchAll()); // returns set of Record objects
var_dump($recordset->asArray()); // returns array of arrays

// iterate and update
while ($record = $recordset->fetch()) {
    var_dump($record->getId()); // rec**********
    var_dump($record->getFields()); // [id => 1, name => Ivan, email => ivan@test.tld]

    $record->setFields(['name' => 'Ivan the 1st']);
    $client->table($tableName)->update($record);
}

// pagination
$query = $client->table($tableName)
    ->select('id')
    ->orderBy(['id' => 'desc'])
    ->paginate(50); // limit(50) works the same. Default (and maximal) page size is 100

while ($recordset = $query->nextPage()) {
    var_dump($recordset->fetchAll());
}

// remove rows
$records = $client->table($tableName)
    ->select('id', 'email')
    ->where(['email' => 'peter@test.tld'])
    ->execute()
    ->fetchAll();

$client->delete(...$records)->execute();
```

## Throttling

Airtable API is limited to 5 requests per second per base. Client uses simple throttling library to keep this limit.

You can disable this behavior:

```php
$client = new \Zadorin\Airtable\Client($apiKey, $database);
$client->throttling(false);
```

## Debug

Client keeps last request object so you can use this for debugging purposes.

**Be careful with debug information because it contains all HTTP headers including authorization token**

```php
$recordset = $client->table($tableName)->select('*')->execute();
$request = $client->getLastRequest();

$request->getResponseCode(); // http code (int)
$request->getPlainResponse(); // response body (string)
$request->getResponseInfo(); // array provided by curl_getinfo()
```

## ToDo

[x] Insert/update/delete both arrays and records

[x] Delete records

[x] Pagination

[x] Request throttling

[x] Debug mode

[ ] Improve exceptions inheritance

[ ] Clean up code, static analyzis

[ ] Upgrade to PHP8

[ ] Expose test tables

[ ] Complex filter expressions

[ ] Simple joins

## Tests

Fill env variables in `phpunit.xml.dist` and then run

    ./vendor/bin/pest
