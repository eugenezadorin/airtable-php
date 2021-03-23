# Airtable PHP client

## Installation

    composer require zadorin/airtable-php

## Usage

```php
$apiKey = 'key***********';
$database = 'app***********';
$tableName = 'my-table';

$client = \Zadorin\Airtable\Client($apiKey, $database);

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

// remove rows
$records = $client->table($tableName)
    ->select('id', 'email')
    ->where(['email' => 'peter@test.tld'])
    ->execute()
    ->fetchAll();

$client->delete(...$records)->execute();
```

## ToDo

[ ] Insert/update/delete both arrays and records

[x] Delete records

[ ] Pagination

[ ] Complex filter expressions

## Tests

Fill env variables in `phpunit.xml.dist` and then run

    ./vendor/bin/pest
