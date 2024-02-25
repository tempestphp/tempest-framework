<?php

declare(strict_types=1);

use Tempest\Validation\Rules\ShouldBeTrue;

test('should be true', function () {
	$rule = new ShouldBeTrue();

	expect($rule->isValid(false))->toBeFalse();
	expect($rule->isValid('false'))->toBeFalse();
	expect($rule->isValid(0))->toBeFalse();
	expect($rule->isValid('0'))->toBeFalse();
	expect($rule->isValid(true))->toBeTrue();
	expect($rule->isValid('true'))->toBeTrue();
	expect($rule->isValid(1))->toBeTrue();
	expect($rule->isValid('1'))->toBeTrue();
});

test('should be true message', function () {
	$rule = new ShouldBeTrue();

	expect($rule->message())->toBe('Value should represent a boolean true value.');
});
