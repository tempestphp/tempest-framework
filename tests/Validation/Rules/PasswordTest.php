<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Password;

test('defaults', function () {
	$rule = new Password();

	expect($rule->isValid('123456789012'))->toBeTrue();
	expect($rule->isValid('aaaaaaaaaaaa'))->toBeTrue();
});

test('invalid input', function () {
	$rule = new Password();
	expect($rule->isValid(123456789012))->toBeFalse();
	expect($rule->isValid([123456789012]))->toBeFalse();
});

test('minimum', function () {
	$rule = new Password(min: 4);
	expect($rule->isValid('12345'))->toBeTrue();
	expect($rule->isValid('1234'))->toBeTrue();
	expect($rule->isValid('123'))->toBeFalse();
});

test('mixed case', function () {
	$rule = new Password(mixedCase: true);
	expect($rule->isValid('abcdEFGHIJKL'))->toBeTrue();
	expect($rule->isValid('abcdefghijkl'))->toBeFalse();
	expect($rule->isValid('ABCDEFGHIJKL'))->toBeFalse();
});

test('letters', function () {
	$rule = new Password(letters: true);
	expect($rule->isValid('12345678901a'))->toBeTrue();
	expect($rule->isValid('123456789012'))->toBeFalse();
});

test('numbers', function () {
	$rule = new Password(numbers: true);
	expect($rule->isValid('123456789012'))->toBeTrue();
	expect($rule->isValid('1aaaaaaaaaaa'))->toBeTrue();
	expect($rule->isValid('abcdefghijkl'))->toBeFalse();
});

test('symbols', function () {
	$rule = new Password(symbols: true);
	expect($rule->isValid('123456789012@'))->toBeTrue();
	expect($rule->isValid('123456789012'))->toBeFalse();
});

test('message', function () {
	$rule = new Password();
	expect($rule->message())->toBe('Value should contain at least 12 characters');

	$rule = new Password(min: 4);
	expect($rule->message())->toBe('Value should contain at least 4 characters');

	$rule = new Password(mixedCase: true);
	expect($rule->message())->toBe('Value should contain at least 12 characters and at least one uppercase and one lowercase letter');

	$rule = new Password(letters: true);
	expect($rule->message())->toBe('Value should contain at least 12 characters and at least one letter');

	$rule = new Password(numbers: true);
	expect($rule->message())->toBe('Value should contain at least 12 characters and at least one number');

	$rule = new Password(symbols: true);
	expect($rule->message())->toBe('Value should contain at least 12 characters and at least one symbol');

	$rule = new Password(min: 4, mixedCase: true, letters: true, numbers: true, symbols: true);
	expect($rule->message())->toBe('Value should contain at least 4 characters, at least one uppercase and one lowercase letter, at least one number, at least one letter and at least one symbol');
});
