<?php

declare(strict_types=1);

use Tempest\Validation\Rules\DoesNotEndWith;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('rule', function (string $needle, string $stringToTest, bool $expected) {
	$rule = new DoesNotEndWith($needle);

	expect($rule->message())->toBe('Value should not end with ' . $needle);

	expect($rule->isValid($stringToTest))->toEqual($expected);
})->with([
	'should return false if it ends with the given string' => ['test', 'this is a test', false],
	'should return true if it does not end with the given string' => ['test', 'test this is a', true],
	'should return true for an empty string' => ['test', '', true],
	'should return true for a single non-matching character' => ['test', 'a', true],
	'should return false for a matching character string' => ['a', 'banana', false],
	'should return true if it ends with a different string' => [
		'test',
		'this is a test best',
		true,
	],
	'should return true if the string is the reverse of the given string' => ['test', 'tset', true],
	'should return false if the string and given string are the same' => ['test', 'test', false],
]);
