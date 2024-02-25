<?php

declare(strict_types=1);

use Tempest\Support\ArrayHelper;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('unwrap single key', function () {
	expect((new ArrayHelper())->unwrap(['a' => 'a']))->toBe(['a' => 'a']);
});

test('unwrap nested key', function () {
	expect((new ArrayHelper())->unwrap(['a.b' => 'ab']))->toBe([
		'a' => [
			'b' => 'ab',
		],
	]);
});

test('unwrap several items', function () {
	expect((new ArrayHelper())->unwrap(['a' => 'a', 'b' => 'b']))->toBe(['a' => 'a', 'b' => 'b']);
});

test('unwrap nested key multiple items', function () {
	expect((new ArrayHelper())->unwrap(['a.0' => 'x', 'a.1' => 'y']))->toBe([
		'a' => [
			'x',
			'y',
		],
	]);
});

test('unwrap real', function () {
	expect((new ArrayHelper())->unwrap([
		'author.name' => 'Brent',
		'author.books.0.title' => 'a',
		'author.books.1.title' => 'b',
	]))->toBe([
		'author' => [
			'name' => 'Brent',
			'books' => [
				['title' => 'a'],
				['title' => 'b'],
			],
		],
	]);
});

test('to array with nested property', function () {
	expect((new ArrayHelper())->toArray(
		key: 'a.b',
		value: 'ab',
	))->toBe([
		'a' => [
			'b' => 'ab',
		],
	]);
});
