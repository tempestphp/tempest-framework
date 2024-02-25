<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Count;

test('count', function (Count $rule, array $stringToTest, bool $expected) {
	expect($rule->isValid($stringToTest))->toEqual($expected);
})->with([
	'Should invalidate when array length is less than the minimum limit (1)' => [
		new Count(min: 1, max: 5),
		[],
		false,
	],
	'Should validate when array length is exactly the minimum limit (1)' => [
		new Count(min: 1, max: 5),
		[1],
		true,
	],
	'Should validate when array length is between the minimum (1) and maximum (5) limits' => [
		new Count(min: 1, max: 5),
		[1, 2, 3],
		true,
	],
	'Should validate when array length is exactly at the maximum limit (5)' => [
		new Count(min: 1, max: 5),
		[1, 2, 3, 4, 5],
		true,
	],
	'Should invalidate when array length is greater than the maximum limit (5)' => [
		new Count(min: 1, max: 5),
		[1, 2, 3, 4, 5, 6],
		false,
	],
]);

test('returns the proper message based on min and max arguments', function (Count $rule, string $expectedMessage) {
	expect($rule->message())->toEqual($expectedMessage);
})->with([
	'Should provide correct message when both min and max limits are defined (1, 5)' => [
		new Count(min: 1, max: 5),
		'Array should contain between 1 and 5 items',
	],
	'Should provide correct message when only min limit is defined (1)' => [
		new Count(min: 1),
		'Array should contain no less than 1 items',
	],
	'Should provide correct message when only max limit is defined (5)' => [
		new Count(max: 5),
		'Array should contain no more than 5 items',
	],
]);

test('throws an exception if neither min or max is supplied', function () {
	$this->expectException(InvalidArgumentException::class);

	new Count();
});
