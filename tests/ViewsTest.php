<?php

declare(strict_types=1);

use Zadorin\Airtable\Errors\LogicError;
use Zadorin\Airtable\Errors\RequestError;

it('allows to specify view on select query', function () {

	$actual = client()->table('views')
		->select('*')
		->whereView('active tasks')
		->execute();

	$expected = [
		['Id' => 3, 'Name' => 'Learn PHP', 'Priority' => 'high', 'Status' => 'todo'],
		['Id' => 4, 'Name' => 'Learn MySQL', 'Priority' => 'normal', 'Status' => 'todo'],
		['Id' => 2, 'Name' => 'Learn CSS', 'Priority' => 'low', 'Status' => 'in progress'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('can apply additional filters to view', function() {

	$actual = client()->table('views')
		->select('*')
		->whereView('active tasks')
		->andWhere('Status', 'todo')
		->execute();

	$expected = [
		['Id' => 3, 'Name' => 'Learn PHP', 'Priority' => 'high', 'Status' => 'todo'],
		['Id' => 4, 'Name' => 'Learn MySQL', 'Priority' => 'normal', 'Status' => 'todo'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('can override records order inside view', function() {

	$actual = client()->table('views')
		->select('*')
		->whereView('active tasks')
		->andWhere('Status', 'todo')
		->orderBy(['Id' => 'desc'])
		->execute();

	$expected = [
		['Id' => 4, 'Name' => 'Learn MySQL', 'Priority' => 'normal', 'Status' => 'todo'],
		['Id' => 3, 'Name' => 'Learn PHP', 'Priority' => 'high', 'Status' => 'todo'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('can specify view with AND logic', function () {

	$actual = client()->table('views')
		->select('*')
		->where('Priority', 'normal')
		->andWhereView('active tasks')
		->execute();

	$expected = [
		['Id' => 4, 'Name' => 'Learn MySQL', 'Priority' => 'normal', 'Status' => 'todo'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('fails when view specified with OR logic', function() {

	$actual = client()->table('views')
		->select('*')
		->where('Priority', 'normal')
		->orWhereView('active tasks')
		->execute();

})->throws(LogicError::class);

it('can specify selected fields inside view', function() {

	$actual = client()->table('views')
		->select('Name', 'Priority')
		->whereView('active tasks')
		->andWhere('Status', 'todo')
		->execute();

	$expected = [
		['Name' => 'Learn PHP', 'Priority' => 'high'],
		['Name' => 'Learn MySQL', 'Priority' => 'normal'],
	];

	expect($actual->asArray())->toEqual($expected);

});

it('fails when view not found', function() {

	$actual = client()->table('views')
		->select('Name', 'Priority')
		->whereView('this view definitely not exists')
		->andWhere('Status', 'todo')
		->execute();

})->throws(RequestError::class);
