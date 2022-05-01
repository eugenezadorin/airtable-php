<?php

declare(strict_types=1);

use Zadorin\Airtable\Errors\RequestError;
use Zadorin\Airtable\Record;

it('fetches linked record ids as array', function () {

	$query = client()->table('linked_data')
		->select('Name', 'Link')
		->where('Name', 'Foo');

	$result = $query->execute()->asArray();

	expect($result)->toHaveCount(1);
	expect($result[0]['Link'])->toBeArray();

});

it('fetches record ids in linked fields', function () {

	$query = client()->table('linked_data')
		->select('Name', 'Link', 'Linked Name')
		->where('Name', 'Foo');

	$result = $query->execute()->asArray();
	$row = $result[0];

	expect($row['Link'][0])->toStartWith('rec');
	expect($row['Linked Name'][0])->toBe('Bar');

});

it('can filter records by linked field', function () {

	$query = client()->table('linked_data')
		->select('Name', 'Link', 'Linked Name')
		->where('Link', 'Foo');

	$result = $query->execute()->asArray();
	$row = $result[0];

	expect($row['Name'])->toBe('Bar');
	expect($row['Linked Name'][0])->toBe('Foo');

});

it('can filter records by lookup field', function () {

	$query = client()->table('linked_data')
		->select('Name', 'Link', 'Linked Name')
		->where('Linked Name', 'Foo');

	$result = $query->execute()->asArray();
	$row = $result[0];

	expect($row['Name'])->toBe('Bar');
	expect($row['Linked Name'][0])->toBe('Foo');

});

it('fails when adding plain value instead of record id into linked field without typecast', function () {

	$fields = [
		'Name' => sprintf('Brand new name %s', uniqid()),
		'Link' => 'Bar',
	];

	client()
		->table('linked_data')
		->insert($fields)
		->execute();

})->expectException(RequestError::class);

it('allows plain value instead of record id into linked field with typecast', function () {

	$fields = [
		'Name' => sprintf('Brand new name %s', uniqid()),
		'Link' => 'Bar',
	];

	$records = client()
		->table('linked_data')
		->insert($fields)
		->typecast(true)
		->execute();

	$result = $records->toArray();
	$row = $result[0];

	expect($result)->toHaveCount(1);
	expect($row['Link'][0])->toStartWith('rec');
	expect($row['Linked Name'][0])->toBe('Bar');

});


it('properly updates linked field with typecast', function () {

	$client = client()->table('linked_data');

	$uniqName = sprintf('Updating %s', uniqid());

	$inserted = $client
		->insert(['Name' => $uniqName, 'Link' => 'Bar'])
		->typecast(true)
		->execute()
		->fetch();

	expect($inserted)->toBeInstanceOf(Record::class);

	expect($inserted->getFields())->toMatchArray(['Linked Name' => ['Bar']]);

	$patch = new Record(['Link' => 'Baz'], $inserted->getId());

	$updated = $client->update($patch)->typecast()->execute()->fetch();

	expect($updated)->toBeInstanceOf(Record::class);

	expect($updated->getFields())->toMatchArray(['Linked Name' => ['Baz']]);

});

it('fails when inserting data into lookup-fields', function () {

	$fields = [
		'Name' => sprintf('Insert to lookup %s', uniqid()),
		'Link' => 'Bar',
		'Linked Name' => 'Bar',
	];

	client()
		->table('linked_data')
		->insert($fields)
		->typecast()
		->execute();

})->expectException(RequestError::class);

it('allows to send multiple values to linked fields', function () {

	$fields = [
		'Name' => sprintf('Sending multiple values %s', uniqid()),
		'Link' => ['Bar', 'Baz'],
	];

	$result = client()
		->table('linked_data')
		->insert($fields)
		->typecast()
		->execute()
		->fetch();

	expect($result)->toBeInstanceOf(Record::class);
	expect($result->getFields())->toMatchArray(['Linked Name' => ['Bar', 'Baz']]);

});
