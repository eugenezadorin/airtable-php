<?php

declare(strict_types=1);

it('can filter records by exact date', function () {

	$query = client()->table('date_filtering')
		->select('id', 'my_iso_date', 'my_iso_datetime')
		->whereDate('my_iso_date', new DateTimeImmutable('2022-03-08'))
		->orderBy(['id' => 'asc']);

	$actual = $query->execute();

	$expected = [
		['id' => 1, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T19:15:00.000Z'],
		['id' => 2, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T07:00:00.000Z'],
	];

	expect($actual->asArray())->toEqual($expected);
});


it('can filter records by date from', function () {

	$query = client()->table('date_filtering')
		->select('id', 'my_iso_date', 'my_iso_datetime')
		->whereDate('my_iso_date', '>', '2022-03-08')
		->orderBy(['id' => 'asc']);

	$actual = $query->execute();

	$expected = [
		['id' => 3, 'my_iso_date' => '2022-03-18', 'my_iso_datetime' => '2022-05-19T16:00:00.000Z'],
		['id' => 5, 'my_iso_date' => '2022-05-01', 'my_iso_datetime' => '2022-05-03T10:37:00.000Z'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('can filter records by date including borders', function () {

	$query = client()->table('date_filtering')
		->select('id', 'my_iso_date', 'my_iso_datetime')
		->whereDate('my_iso_date', '>=', '2022-03-08')
		->orderBy(['id' => 'asc']);

	$actual = $query->execute();

	$expected = [
		['id' => 1, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T19:15:00.000Z'],
		['id' => 2, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T07:00:00.000Z'],
		['id' => 3, 'my_iso_date' => '2022-03-18', 'my_iso_datetime' => '2022-05-19T16:00:00.000Z'],
		['id' => 5, 'my_iso_date' => '2022-05-01', 'my_iso_datetime' => '2022-05-03T10:37:00.000Z'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('can filter records by date to', function () {

	$query = client()->table('date_filtering')
		->select('id', 'my_iso_date', 'my_iso_datetime')
		->whereDate('my_iso_date', '<', new DateTimeImmutable('2022-03-18'))
		->orderBy(['id' => 'asc']);

	$actual = $query->execute();

	$expected = [
		['id' => 1, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T19:15:00.000Z'],
		['id' => 2, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T07:00:00.000Z'],
		['id' => 4, 'my_iso_date' => '2022-03-01', 'my_iso_datetime' => '2022-03-31T10:36:00.000Z'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('can filter records by date interval', function () {

	$dateFrom = new DateTimeImmutable('2022-03-01');
	$dateTo = new DateTimeImmutable('2022-03-18');

	$query = client()->table('date_filtering')
		->select('id', 'my_iso_date', 'my_iso_datetime')
		->whereDateBetween('my_iso_date', $dateFrom, $dateTo)
		->orderBy(['id' => 'asc']);

	$actual = $query->execute();

	$expected = [
		['id' => 1, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T19:15:00.000Z'],
		['id' => 2, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T07:00:00.000Z'],
		['id' => 3, 'my_iso_date' => '2022-03-18', 'my_iso_datetime' => '2022-05-19T16:00:00.000Z'],
		['id' => 4, 'my_iso_date' => '2022-03-01', 'my_iso_datetime' => '2022-03-31T10:36:00.000Z'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('can filter records by datetime', function () {
	$query = client()->table('date_filtering')
		->select('id', 'my_iso_date', 'my_iso_datetime')
		->whereDateTime('my_iso_datetime', new DateTimeImmutable('2022-05-03T10:37:00.000Z'))
		->orderBy(['id' => 'asc']);

	$actual = $query->execute();

	$expected = [
		['id' => 5, 'my_iso_date' => '2022-05-01', 'my_iso_datetime' => '2022-05-03T10:37:00.000Z'],
	];

	expect($actual->asArray())->toEqual($expected);
});

it('can filter records by datetime border', function () {
	$query = client()->table('date_filtering')
		->select('id', 'my_iso_date', 'my_iso_datetime')
		->whereDateTime('my_iso_datetime', '>', '2022-05-01 00:00:00')
		->orderBy(['id' => 'asc']);

	$actual = $query->execute();

	$expected = [
		['id' => 3, 'my_iso_date' => '2022-03-18', 'my_iso_datetime' => '2022-05-19T16:00:00.000Z'],
		['id' => 5, 'my_iso_date' => '2022-05-01', 'my_iso_datetime' => '2022-05-03T10:37:00.000Z'],
	];

	expect($actual->asArray())->toEqual($expected);
});

it('can filter records by datetime including borders', function () {
	$query = client()->table('date_filtering')
		->select('id', 'my_iso_date', 'my_iso_datetime')
		->whereDateTime('my_iso_datetime', '<=', '2022-04-01T19:15:00.000Z')
		->orderBy(['id' => 'asc']);

	$actual = $query->execute();

	$expected = [
		['id' => 1, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T19:15:00.000Z'],
		['id' => 2, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T07:00:00.000Z'],
		['id' => 4, 'my_iso_date' => '2022-03-01', 'my_iso_datetime' => '2022-03-31T10:36:00.000Z'],
	];

	expect($actual->asArray())->toEqual($expected);
});

it('can filter records by datetime interval', function () {

	$dateTimeFrom = new DateTimeImmutable('2022-04-01T06:00:00.000Z');
	$dateTimeTo = new DateTimeImmutable('2022-04-01T20:00:00.000Z');

	$query = client()->table('date_filtering')
		->select('id', 'my_iso_date', 'my_iso_datetime')
		->whereDateTimeBetween('my_iso_datetime', $dateTimeFrom, $dateTimeTo)
		->orderBy(['id' => 'asc']);

	$actual = $query->execute();

	$expected = [
		['id' => 1, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T19:15:00.000Z'],
		['id' => 2, 'my_iso_date' => '2022-03-08', 'my_iso_datetime' => '2022-04-01T07:00:00.000Z'],
	];

	expect($actual->asArray())->toEqual($expected);

});

