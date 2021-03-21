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
        ['name' => 'foo', 'value' => 'bar'],
        ['name' => 'baz', 'value' => 'qux']
    ])
    ->execute();

// fetch data
$recordset = $client->table($tableName)
    ->select('id', 'name', 'value')
    ->where(['name' => 'foo', 'value' => 'bar'])
    ->orderBy(['id' => 'desc'])
    ->limit(10)
    ->execute();

var_dump($recordset->fetchAll());
var_dump($recordset->asArray());

// iterate and update
while ($record = $recordset->fetch()) {
    var_dump($record->getId()); // rec**********
    var_dump($record->getFields()); // [id => 1, name => foo, value => bar]

    $record->setFields(['name' => 'New Name']);
    $client->table($tableName)->update($record);
}
```

## ToDo

[ ] Insert and update both arrays and records
[ ] Delete records
[ ] Pagination
[ ] Complex filter expressions

## Tests

Fill env variables in `phpunit.xml.dist` and then run

    ./vendor/bin/pest
