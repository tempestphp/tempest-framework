<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Timezone;

test('timezone', function () {
	$rule = new Timezone();

	expect($rule->message())->toBe('Value should be a valid timezone');

	expect($rule->isValid('invalid_timezone'))->toBeFalse();
	expect($rule->isValid('Asia/Sydney'))->toBeFalse();
	expect($rule->isValid('America/New_York'))->toBeTrue();
	expect($rule->isValid('Europe/London'))->toBeTrue();
	expect($rule->isValid('Europe/Paris'))->toBeTrue();
	expect($rule->isValid('UTC'))->toBeTrue();
});

test('timezone with country code', function () {
	$rule = new Timezone(DateTimeZone::PER_COUNTRY, 'AU');

	expect($rule->isValid('America/New_York'))->toBeFalse();
	expect($rule->isValid('Australia/Sydney'))->toBeTrue();
	expect($rule->isValid('Australia/Melbourne'))->toBeTrue();

	$rule = new Timezone(DateTimeZone::PER_COUNTRY, 'US');

	expect($rule->isValid('Europe/Paris'))->toBeFalse();
	expect($rule->isValid('America/New_York'))->toBeTrue();
	expect($rule->isValid('America/Los_Angeles'))->toBeTrue();
	expect($rule->isValid('America/Chicago'))->toBeTrue();
});

test('timezone with group', function () {
	$rule = new Timezone(DateTimeZone::ASIA);

	expect($rule->isValid('Africa/Nairobi'))->toBeFalse();
	expect($rule->isValid('Asia/Tokyo'))->toBeTrue();
	expect($rule->isValid('Asia/Hong_Kong'))->toBeTrue();
	expect($rule->isValid('Asia/Singapore'))->toBeTrue();

	$rule = new Timezone(DateTimeZone::INDIAN);

	expect($rule->isValid('Europe/Paris'))->toBeFalse();
	expect($rule->isValid('Indian/Reunion'))->toBeTrue();
	expect($rule->isValid('Indian/Comoro'))->toBeTrue();
});
