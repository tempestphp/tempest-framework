<?php

declare(strict_types=1);

use Tempest\Validation\Rules\EndsWith;

test('ends with', function () {
	$rule = new EndsWith(needle: 'ab');

	expect($rule->message())->toBe('Value should end with ab');

	expect($rule->isValid('ab'))->toBeTrue();
	expect($rule->isValid('cab'))->toBeTrue();
	expect($rule->isValid('b'))->toBeFalse();
	expect($rule->isValid('3434'))->toBeFalse();
});
