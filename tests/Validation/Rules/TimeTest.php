<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Time;

test('time', function () {
	$rule = new Time();

	expect($rule->message())->toBe('Value should be a valid time in the format of hh:mm xm');

	expect($rule->isValid('0001'))->toBeFalse();
	expect($rule->isValid('01:00'))->toBeFalse();
	expect($rule->isValid('200'))->toBeFalse();
	expect($rule->isValid('01:60 a.m.'))->toBeFalse();
	expect($rule->isValid('23:00'))->toBeFalse();
	expect($rule->isValid('2300'))->toBeFalse();

	expect($rule->isValid('01:00 am'))->toBeTrue();
	expect($rule->isValid('01:00 a.m.'))->toBeTrue();
	expect($rule->isValid('01:00 A.M.'))->toBeTrue();
	expect($rule->isValid('01:00 AM'))->toBeTrue();
	expect($rule->isValid('01:00 pm'))->toBeTrue();
	expect($rule->isValid('01:00 p.m.'))->toBeTrue();
	expect($rule->isValid('01:00 P.M.'))->toBeTrue();
	expect($rule->isValid('01:00 PM'))->toBeTrue();
	expect($rule->isValid('01:59 a.m.'))->toBeTrue();
});

test('military time', function () {
	$rule = new Time(twentyFourHour: true);

	expect($rule->message())->toBe('Value should be a valid time in the 24-hour format of hh:mm');

	expect($rule->isValid('2400'))->toBeFalse();
	expect($rule->isValid('01:00 am'))->toBeFalse();
	expect($rule->isValid('01:00 a.m.'))->toBeFalse();
	expect($rule->isValid('01:00 A.M.'))->toBeFalse();
	expect($rule->isValid('01:00 AM'))->toBeFalse();
	expect($rule->isValid('01:00 pm'))->toBeFalse();
	expect($rule->isValid('01:00 p.m.'))->toBeFalse();
	expect($rule->isValid('01:00 P.M.'))->toBeFalse();
	expect($rule->isValid('01:00 PM'))->toBeFalse();
	expect($rule->isValid('01:59 a.m.'))->toBeFalse();
	expect($rule->isValid('24:00'))->toBeFalse();

	expect($rule->isValid('23:00'))->toBeTrue();
	expect($rule->isValid('2300'))->toBeTrue();
	expect($rule->isValid('0100'))->toBeTrue();
	expect($rule->isValid('0200'))->toBeTrue();
	expect($rule->isValid('0300'))->toBeTrue();
	expect($rule->isValid('0400'))->toBeTrue();
	expect($rule->isValid('0500'))->toBeTrue();
	expect($rule->isValid('0600'))->toBeTrue();
	expect($rule->isValid('0700'))->toBeTrue();
	expect($rule->isValid('0800'))->toBeTrue();
	expect($rule->isValid('0900'))->toBeTrue();
	expect($rule->isValid('1000'))->toBeTrue();
	expect($rule->isValid('1100'))->toBeTrue();
	expect($rule->isValid('1200'))->toBeTrue();
	expect($rule->isValid('1300'))->toBeTrue();
	expect($rule->isValid('1400'))->toBeTrue();
	expect($rule->isValid('1500'))->toBeTrue();
	expect($rule->isValid('1600'))->toBeTrue();
	expect($rule->isValid('1700'))->toBeTrue();
	expect($rule->isValid('1800'))->toBeTrue();
	expect($rule->isValid('1900'))->toBeTrue();
	expect($rule->isValid('2000'))->toBeTrue();
	expect($rule->isValid('2100'))->toBeTrue();
	expect($rule->isValid('2200'))->toBeTrue();
	expect($rule->isValid('2300'))->toBeTrue();
	expect($rule->isValid('2340'))->toBeTrue();
});
