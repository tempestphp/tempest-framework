<?php

declare(strict_types=1);

use Tempest\Validation\Rules\IPAddress;

test('ip address', function () {
	$rule = new IPAddress();

	expect($rule->message())->toBe('Value should be a valid IP Address');
	expect($rule->isValid('192.168.0.1'))->toBeTrue();
	expect($rule->isValid('10.0.0.1'))->toBeTrue();
	expect($rule->isValid('172.16.0.1'))->toBeTrue();
	expect($rule->isValid('2001:0db8:85a3:0000:0000:8a2e:0370:7334'))->toBeTrue();
	expect($rule->isValid('2001:db8:85a3::8a2e:370:7334'))->toBeTrue();

	expect($rule->isValid('256.0.0.1'))->toBeFalse();
	expect($rule->isValid('300.168.0.1'))->toBeFalse();
	expect($rule->isValid('192.168.0'))->toBeFalse();
	expect($rule->isValid('192.168.0.1.2'))->toBeFalse();
	expect($rule->isValid('192.168.0.1/24'))->toBeFalse();
});
