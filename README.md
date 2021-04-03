# Airtable PHP client

## Installation

    composer require zadorin/airtable-php

## Usage

### Basic setup

```php
$apiKey = 'key***********';
$database = 'app***********';
$tableName = 'my-table';

$client = new \Zadorin\Airtable\Client($apiKey, $database);
```

You can find API key in your [account settings](https://airtable.com/account) and database name in [API Docs](https://airtable.com/api).

### Insert some rows

```php
$client->table($tableName)
    ->insert([
        ['name' => 'Ivan', 'email' => 'ivan@test.tld'],
        ['name' => 'Peter', 'email' => 'peter@test.tld']
    ])
    ->execute();
```

### Fetch data

```php
$recordset = $client->table($tableName)
    ->select('id', 'name', 'email')
    ->where(['name' => 'Ivan', 'email' => 'ivan@test.tld'])
    ->orderBy(['id' => 'desc'])
    ->limit(10)
    ->execute();

var_dump($recordset->fetchAll()); // returns set of Record objects
var_dump($recordset->asArray()); // returns array of arrays
```

### Iterate and update records

```php
while ($record = $recordset->fetch()) {
    var_dump($record->getId()); // rec**********
    var_dump($record->getFields()); // [id => 1, name => Ivan, email => ivan@test.tld]

    $record->setFields(['name' => 'Ivan the 1st']);
    $client->table($tableName)->update($record);
}
```

### Pagination

```php
$query = $client->table($tableName)
    ->select('id')
    ->orderBy(['id' => 'desc'])
    ->paginate(50); // limit(50) works the same. Default (and maximal) page size is 100

while ($recordset = $query->nextPage()) {
    var_dump($recordset->fetchAll());
}
```

### Remove rows

```php
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

## Exceptions

All package exceptions inherits from common `Zadorin\Airtable\Errors\AirtableError` class.

Also you may be interested in `Zadorin\Airtable\Errors\RequestError` which contains last request instance:

```php
try {
    $inserted = $client->table($tableName)->insert()->execute();
} catch (RequestError $e) {
    
    // catch Airtable responses here
    var_dump($e->getMessage());
    var_dump($e->getLastRequest()->getResponseInfo());

} catch (AirtableError $e) {

    // catch package errors. In that case it will be "No records specified for insert"

}
```

## ToDo

[x] Insert/update/delete both arrays and records

[x] Delete records

[x] Pagination

[x] Request throttling

[x] Debug mode

[x] Static analyzis

[x] Expose test tables

[x] Improve exceptions inheritance

[x] Test on PHP8

[x] Improve readme (split examples)

[x] Set 0.0.1 tag

[ ] Publish on packagist

[ ] Complex filter expressions

[ ] Simple joins

[ ] Clean up code

## Known problems

Client uses `ext-curl` to make requests and `ext-json` to encode/decode results. Make sure this php extensions installed and properly configured.

If you see `SSL certificate problem: unable to get local issuer certificate` you probably have to configure option `curl.cainfo` in your `php.ini`. [Source](https://stackoverflow.com/questions/28858351/php-ssl-certificate-error-unable-to-get-local-issuer-certificate)

## License and contributing

MIT License. Any feedback is highly appreciated — welcome to [issues](https://github.com/eugenezadorin/airtable-php/issues). 

If you want to send pull request make sure all tests are pass.

## Tests

Copy this [readonly test database](https://airtable.com/shrs2bB37sScbDuLX) into your Airtable account, then fill env variables specified in `phpunit.xml.dist`. 

And finally run test suite:

    ./vendor/bin/pest

It's also recommended to use static analysis tool to avoid errors:

    ./vendor/bin/psalm
