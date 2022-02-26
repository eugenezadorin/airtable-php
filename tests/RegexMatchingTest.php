<?php

declare(strict_types=1);

it('can filter records by substring', function () {
	$actual = client()->table('regexp_selections')
		->select('Name', 'Value')
		->where('Name', 'like', 'bar')
		->orderBy(['Value' => 'asc'])
		->execute();

	$expected = [
		['Name' => 'barbazqux', 'Value' => '300'],
		['Name' => 'bazbarqux', 'Value' => '400'],
		['Name' => 'bazquxbar', 'Value' => '500'],
	];

	expect($actual->asArray())->toEqual($expected);
});

it('matches case when filtering by substring', function () {
	$actual = client()->table('regexp_selections')
		->select('Name', 'Value')
		->where('Name', 'like', 'fo')
		->orderBy(['Value' => 'asc'])
		->execute();

	$expected = [
		['Name' => 'foo', 'Value' => '100'],
	];

	expect($actual->asArray())->toEqual($expected);
});

it('can combine miltiple where like conditions', function () {
	$actual = client()->table('regexp_selections')
		->select('Name', 'Value')
		->where('Name', 'like', 'fo')
		->orWhere('Name', 'like', 'FO')
		->orderBy(['Value' => 'asc'])
		->execute();

	$expected = [
		['Name' => 'foo', 'Value' => '100'],
		['Name' => 'FOO', 'Value' => '200'],
	];

	expect($actual->asArray())->toEqual($expected);
});

it('can filter cells that starts with search query', function () {
	$actual = client()->table('regexp_selections')
		->select('Name', 'Value')
		->where('Name', 'like', 'baz%')
		->orderBy(['Value' => 'asc'])
		->execute();

	$expected = [
		['Name' => 'bazbarqux', 'Value' => '400'],
		['Name' => 'bazquxbar', 'Value' => '500'],
	];

	expect($actual->asArray())->toEqual($expected);
});

it('can filter cells that ends with search query', function () {
	$actual = client()->table('regexp_selections')
		->select('Name', 'Value')
		->where('Name', 'like', '%qux')
		->orderBy(['Value' => 'asc'])
		->execute();

	$expected = [
		['Name' => 'barbazqux', 'Value' => '300'],
		['Name' => 'bazbarqux', 'Value' => '400'],
	];

	expect($actual->asArray())->toEqual($expected);
});

it('can use % wildcard multiple times', function () {
	$actual = client()->table('regexp_selections')
		->select('Name', 'Value')
		->where('Name', 'like', '%ba%ba%ux%')
		->orderBy(['Value' => 'asc'])
		->execute();

	$expected = [
		['Name' => 'barbazqux', 'Value' => '300'],
		['Name' => 'bazbarqux', 'Value' => '400'],
	];

	expect($actual->asArray())->toEqual($expected);
});

it('can use regexp to filter records', function() {
	$actual = client()->table('regexp_selections')
		->select('Name', 'Value')
		->where('Name', 'match', '(?i)fo')
		->orderBy(['Value' => 'asc'])
		->execute();

	$expected = [
		['Name' => 'foo', 'Value' => '100'],
		['Name' => 'FOO', 'Value' => '200'],
	];

	expect($actual->asArray())->toEqual($expected);
});

test('searching case-insensitive emails with regexp', function () {
	$actual = client()->table('regexp_selections')
		->select('Name', 'Value')
		->where('Name', 'match', '(?i)^(.+)@gmail.com$')
		->orderBy(['Value' => 'asc'])
		->execute();

	$expected = [
		['Name' => 'test@gmail.com', 'Value' => '600'],
		['Name' => 'peter@gmail.com', 'Value' => '700'],
		['Name' => 'john@GMAIL.com', 'Value' => '900'],
	];

	expect($actual->asArray())->toEqual($expected);
});

test('searching case-sensitive emails with like keyword', function () {
	$actual = client()->table('regexp_selections')
		->select('Name', 'Value')
		->where('Name', 'like', '%@gmail.com')
		->orderBy(['Value' => 'asc'])
		->execute();

	$expected = [
		['Name' => 'test@gmail.com', 'Value' => '600'],
		['Name' => 'peter@gmail.com', 'Value' => '700'],
	];

	expect($actual->asArray())->toEqual($expected);
});