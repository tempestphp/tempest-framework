<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Uuid;

test('uuid', function () {
	$rule = new Uuid();

	expect($rule->message())->toBe('Value should contain a valid UUID');

	expect($rule->isValid('string_123'))->toBeFalse();

	// UUID v1
	expect($rule->isValid('CB2F46B4-D0C6-11EE-A506-0242AC120002'))->toBeTrue();
	expect($rule->isValid('cb2f46b4-d0c6-11ee-a506-0242ac120002'))->toBeTrue();

	// UUID v4
	expect($rule->isValid('0EC29141-3D58-4187-B664-2D93B7DA0D31'))->toBeTrue();
	expect($rule->isValid('0ec29141-3d58-4187-b664-2d93b7da0d31'))->toBeTrue();

	// UUID v7
	expect($rule->isValid('018DCC19-7E65-7C4B-9B14-9A11DF3E0FDB'))->toBeTrue();
	expect($rule->isValid('018dcc19-7e65-7c4b-9b14-9a11df3e0fdb'))->toBeTrue();
});
