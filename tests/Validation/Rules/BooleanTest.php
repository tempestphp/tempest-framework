<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Boolean;

test('boolean', function () {
	$rule = new Boolean();

	expect($rule->isValid(true))->toBeTrue();
	expect($rule->isValid('true'))->toBeTrue();
	expect($rule->isValid(1))->toBeTrue();
	expect($rule->isValid('1'))->toBeTrue();
	expect($rule->isValid(false))->toBeTrue();
	expect($rule->isValid('false'))->toBeTrue();
	expect($rule->isValid(0))->toBeTrue();
	expect($rule->isValid('0'))->toBeTrue();
	expect($rule->isValid(5))->toBeFalse();
	expect($rule->isValid(2.5))->toBeFalse();
	expect($rule->isValid('string'))->toBeFalse();
});

test('boolean message', function () {
	$rule = new Boolean();

	expect($rule->message())->toBe('Value should represent a boolean value');
});
