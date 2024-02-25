<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Between;

test('between', function () {
	$rule = new Between(min: 0, max: 10);

	expect($rule->message())->toBe('Value should be between 0 and 10');

	expect($rule->isValid(0))->toBeTrue();
	expect($rule->isValid(10))->toBeTrue();
	expect($rule->isValid(5))->toBeTrue();
	expect($rule->isValid(11))->toBeFalse();
	expect($rule->isValid(-1))->toBeFalse();
});
