<?php

declare(strict_types=1);

use Zadorin\Airtable\Client;
use Zadorin\Airtable\Errors\InvalidArgument;
use Zadorin\Airtable\Errors\MethodNotExists;

it('allows only non-empty keys', function () {

	Client::macro('', function() {});

})->expectException(InvalidArgument::class);

it('fails on non-existing macro', function () {

	$actual = client()->table('simple_selections')
		->select('Name', 'Value')
		->whereCustomMethodDefinitelyNotExists()
		->execute();

})->expectException(MethodNotExists::class);

it('fails prevents macro with and- prefix', function () {

	Client::macro('andWhereValue', function () {});

})->expectException(InvalidArgument::class);

it('fails prevents macro with or- prefix', function () {

	Client::macro('orWhereValue', function () {});

})->expectException(InvalidArgument::class);

it('can create custom where statements', function () {

	Client::macro('whereValueIsBar', function () {
		return $this->where('Value', 'Bar');
	});

	$actual = client()->table('simple_selections')
		->select('Name', 'Value')
		->whereValueIsBar()
		->execute();

	$expected = [
		['Name' => 'Foo', 'Value' => 'Bar'],
		['Name' => 'Qux', 'Value' => 'Bar'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('can combine macro', function () {

	Client::macro('whereValueIsBar', function () {
		return $this->where('Value', 'Bar');
	});

	Client::macro('whereValueIsBarAndNameIsFoo', function () {
		return $this->whereValueIsBar()->andWhere('Name', 'Foo');
	});

	$actual = client()->table('simple_selections')
		->select('Name', 'Value')
		->whereValueIsBarAndNameIsFoo()
		->execute();

	$expected = [
		['Name' => 'Foo', 'Value' => 'Bar'],
	];

	expect($actual->asArray())->toEqual($expected);

});

test('macro supports logic modifiers', function () {

	Client::macro('whereValueIsBar', function () {
		return $this->where('Value', 'Bar');
	});

	$actual = client()->table('simple_selections')
		->select('Name', 'Value')
		->where('Name', 'Qux')
		->orWhereValueIsBar()
		->execute();

	$expected = [
		['Name' => 'Foo', 'Value' => 'Bar'],
		['Name' => 'Qux', 'Value' => 'Bar'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('supports passing variables to closure', function () {

	Client::macro('hasName', function (string $name) {
		return $this->where('Name', $name);
	});

	$actual = client()->table('simple_selections')
		->select('Name', 'Value')
		->hasName('Baz')
		->orHasName('Qux')
		->execute();

	$expected = [
		['Name' => 'Baz', 'Value' => '123'],
		['Name' => 'Qux', 'Value' => 'Bar'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('works with raw formula', function () {

	Client::macro('whereMay', function($year) {
		$this->whereRaw("AND(IS_AFTER(my_iso_date, '$year-04-30 23:59:59'), IS_BEFORE(my_iso_date, '$year-06-01 00:00:00'))");
	});

	$query = client()->table('date_filtering')
		->select('id', 'my_iso_date', 'my_iso_datetime')
		->whereMay(2022)
		->orderBy(['id' => 'asc']);

	$actual = $query->execute();

	$expected = [
		['id' => 5, 'my_iso_date' => '2022-05-01', 'my_iso_datetime' => '2022-05-03T10:37:00.000Z'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('works with dates', function () {

	Client::macro('whereMay', function($year) {
		$this->whereDate('my_iso_date', '>=', "$year-05-01");
		$this->whereDate('my_iso_date', '<', "$year-06-01");
	});

	$query = client()->table('date_filtering')
		->select('id', 'my_iso_date', 'my_iso_datetime')
		->whereMay(2022)
		->orderBy(['id' => 'asc']);

	$actual = $query->execute();

	$expected = [
		['id' => 5, 'my_iso_date' => '2022-05-01', 'my_iso_datetime' => '2022-05-03T10:37:00.000Z'],
	];

	expect($actual->asArray())->toEqual($expected);

});