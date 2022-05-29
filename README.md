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

### Fetch multiple rows

```php
$recordset = $client->table($tableName)
    ->select('id', 'name', 'email') // you can use shortcut select('*') to fetch all columns
    ->where(['name' => 'Ivan', 'email' => 'ivan@test.tld'])
    ->orderBy(['id' => 'desc'])
    ->limit(10)
    ->execute();

var_dump($recordset->fetchAll()); // returns set of Record objects
var_dump($recordset->asArray()); // returns array of arrays
```

### Fetch specific rows by record id

```php
$recordset = $client->table($tableName)
    ->find('rec1*******', 'rec2*******')
    ->execute();
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
    ->select('*')
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

## Complex filters

You can build complex formulas to filter records, but be careful, because formula applies to each record and can slow down your query.

Assume we prepared following query object:

```php
$query = $client->table('my-table')->select('*');
```

### Query builder

The following lines give the same results:

```php
$query->where(['email' => 'ivan@test.tld']);
$query->where('email', 'ivan@test.tld');
$query->where('email', '=', 'ivan@test.tld');
```

You can use different logical operators:

```php
$query->where('email', '!=', 'ivan@test.tld');
$query->where('code', '>', 100);
```

It's possible to concat multiple where statements:

```php
$query->where([
    ['code', '>', 100],
    ['code', '<', 200],
]);
```

Or chain methods to achieve the same result:

```php
$query->where('code', '>', 100)->andWhere('code', '<', 200);
```

### OR-logic

```php
$query->where('name', 'Ivan')->orWhere('id', 5);
```

Methods `where()`, `andWhere()`, `orWhere()` use the same signature, so you can combine them:

```php
$query->where('code', '>', 100)
    ->andWhere('code', '<', 500)
    ->orWhere([
        ['code', '<', 100],
        ['id', '=', 5]
    ]);
```

### Regex filtering

Besides logical operators, you can use keywords `like` and `match` in your where-statements.

Keyword `match` allows you to apply `REGEXP_MATCH()` function to your filter formula. 
Airtable's REGEX functions are implemented using the [RE2 regular expression library](https://github.com/google/re2/wiki/Syntax),
so be sure that syntax of your regular expression is correct:

```php
// look for emails, matching @gmail.com in case-insensitive way
$query->where('email', 'match', '(?i)^(.+)@gmail.com$');
```

Keyword `like` also uses `REGEXP_MATCH()` under the hood, but provides more SQL-like syntax:

```php
// look for emails, which ends with @gmail.com
$query->where('email', 'like', '%@gmail.com');

// look for names, which starts with Ivan
$query->where('name', 'like', 'Ivan%');

// look for urls, which contains substring (both variants below works the same):
$query->where('url', 'like', '%github%');
$query->where('url', 'like', 'github');
```

Please note, that `like` is case-sensitive, so if you want to ignore case, you'd better use `match` with `i`-flag.

### Date filtering

Library provides few methods to filter records by date and time:

```php
$query->whereDate('birthdate', new \DateTimeImmutable('2022-03-08'));
$query->whereDateTime('meeting_start', '2022-04-01 11:00:00');
```

First parameter is your column name.

You can pass `DateTimeImmutable` object or datetime string, which will be cast into `DateTimeImmutable` automatically.

You can filter records by date range instead of strict equality:

```php
$query
    ->whereDate('birthdate', '>=', new \DateTimeImmutable('2022-03-01'))
    ->andWhereDate('birthdate', '<', new \DateTimeImmutable('2022-04-01')); 
```

There are shortcuts for that purpose:

```php
$query->whereDateBetween('birthdate', '2022-03-01', '2022-03-31'); // left and right borders included!
$query->whereDateTimeBetween('meeting_start', '2022-04-01 11:00:00', '2022-04-01 15:00:00');
```

When searching by date (not datetime), library applies range filter under the hood.
For example, `$query->whereDate('meeting', '2022-03-08')` will actually search records between `2022-03-08 00:00:00` and `2022-03-08 23:59:59`, 
including left and right borders.

Please note that the library does not perform any timezone conversions, so most reliable solution is to specify GMT timezone in your `DateTimeImmutable` objects, 
and set flag `Use the same time zone (GMT) for all collaborators` in your datetime column settings.

### Raw formula

You can see what exact formula was built:

```php
$query->where([
    ['Code', '>', 100],
    ['Code', '<', 300]
])
->orWhere('Name', 'Qux');
    
$query->getFormula(); // OR(AND({Code}>'100', {Code}<'300'), {Name}='Qux')
```

Also, you can filter records by raw formula:

```php
$query->whereRaw("OR( AND({Code}>'100', {Code}<'300'), {Name}='Qux' )");
```

All query builder methods are used to make raw formula under the hood.
It means that if the functionality of query builder is not enough, you can always use raw formula instead.

Note that library don't validate raw formulas so you can get exception from Airtable API.

### View

Sometimes it is more convenient to create a specific table view with predefined sorting and filters, 
instead of building a complex query in the source code.

Assuming you have `tasks` table and `active tasks` view containing only active tasks ordered by priority:

```php
$records = $client->table('tasks')
    ->select('*')
    ->whereView('active tasks')
    ->execute();
```

You can combine view and additional filters, specify subset of selected fields and override order just like normal select query:

```php
$records = $client->table('tasks')
    ->select('Name', 'Priority')
    ->whereView('active tasks')
    ->andWhere('Status', 'todo')
    ->orderBy(['Id' => 'desc'])
    ->execute();
```

You can use alias `andWhereView()` but method `orWhereView()` will throw `LogicError`. 
This is because view is not actually part of the filter formula, it always works like "view AND formula", 
so you can't use `OR` operator here.

Also note that if view not exists `RequestError` exception will be thrown.

### Macros

You can extend query builder methods with your own using macros:

```php
\Zadorin\Airtable\Client::macro('whereCanDriveCar', function() {
    $this->where('age', '>=', 21);
});

$query->where('state', 'Florida')->andWhereCanDriveCar();
```

Macro name must not start with `or`/`and`. These logic prefixes are reserved and handles automatically.

Context `$this` inside macro callback references to query builder instance. It allows you to use other query builder methods or even other macros:

```php
Client::macro('whereStateIsFlorida', function () {
    $this->where('state', 'Florida');
});

Client::macro('canDriveCar', function() {
    $this->where('age', '>=', 21);
});

Client::macro('whereFloridaDriver', function() {
    $this->whereStateIsFlorida()->andCanDriveCar();
});
```

You can pass variables into macro callback:

```php
Client::macro('whereName', function ($name) {
    $this->where('Name', '=', $name);
});

$query->whereName('Ivan')->orWhereName('John');
```

And of course you can use raw formula to build something more complex:

```php
Client::macro('whereBornInMay', function($year) {
    $this->whereRaw("AND(IS_AFTER(birthdate, '$year-04-30 23:59:59'), IS_BEFORE(birthdate, '$year-06-01 00:00:00'))");
});
```

But remember that raw formula overrides other query builder setup.

## Typecast

Airtable supports linked fields, which references other rows from current or another table. 
Assume you have `users` table where `contacts` field is a link to row in another table.

By default, you have to specify concrete row ID while inserting or updating such fields:

```php
$client
    ->table('users')
    ->insert(['name' => 'Ivan', 'contacts' => 'recSPVbdx5vXwyLoH'])
    ->execute();
```

It's not very handy, so Airtable API supports `typecast` parameter, which enables automatic data conversion from string values.

Automatic conversion is disabled by default to ensure data integrity, but sometimes it may be helpful.

This is how you can enable that feature:

```php
$client
    ->table('users')
    ->insert(['name' => 'Ivan', 'contacts' => 'ivan@test.tld'])
    ->typecast(true) // true is default value and can be skipped
    ->execute();
```

Update queries works the same.

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
