<?php

declare(strict_types=1);

use Tempest\Validation\Rules\RegEx;

test('regex rule', function () {
	$rule = new RegEx('/^[aA][bB]$/');

	expect($rule->message())->toBe('The value must match the regular expression pattern: /^[aA][bB]$/');

	expect($rule->isValid('cd'))->toBeFalse();
	expect($rule->isValid('za'))->toBeFalse();

	expect($rule->isValid('ab'))->toBeTrue();
	expect($rule->isValid('AB'))->toBeTrue();
	expect($rule->isValid('Ab'))->toBeTrue();
});
