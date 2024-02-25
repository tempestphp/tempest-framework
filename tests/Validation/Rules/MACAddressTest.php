<?php

declare(strict_types=1);

use Tempest\Validation\Rules\MACAddress;

test('ip address', function () {
	$rule = new MACAddress();

	expect($rule->message())->toBe('Value should be a valid MAC Address');
	expect($rule->isValid('00:1A:2B:3C:4D:5E'))->toBeTrue();
	expect($rule->isValid('01-23-45-67-89-AB'))->toBeTrue();
	expect($rule->isValid('A1:B2:C3:D4:E5:F6'))->toBeTrue();
	expect($rule->isValid('a1:b2:c3:d4:e5:f6'))->toBeTrue();
	expect($rule->isValid('FF:FF:FF:FF:FF:FF'))->toBeTrue();

	expect($rule->isValid('00:1A:2B:3C:4D'))->toBeFalse();
	expect($rule->isValid('01-23-45-67-89-AB-CD'))->toBeFalse();
	expect($rule->isValid('A1:B2:C3:D4:E5:G6'))->toBeFalse();
	expect($rule->isValid('a1:b2:c3:d4:e5:f6:7'))->toBeFalse();
	expect($rule->isValid('FF:FF:FF:FF:FF'))->toBeFalse();
});
