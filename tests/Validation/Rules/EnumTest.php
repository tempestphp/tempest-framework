<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Enum;
use Tests\Tempest\TestCase;
use Tests\Tempest\Validation\Rules\Fixtures\SomeBackedEnum;
use Tests\Tempest\Validation\Rules\Fixtures\SomeEnum;

uses(TestCase::class);

test('validating enums', function () {
	$rule = new Enum(SomeEnum::class);

	expect($rule->message())->toBe(sprintf(
		'The value must be a valid enumeration [%s] case',
		SomeEnum::class
	));

	expect($rule->isValid('NOPE_NOT_HERE'))->toBeFalse();
	expect($rule->isValid('NOPE_NOT_HERE_EITHER'))->toBeFalse();
	expect($rule->isValid('VALUE_1'))->toBeTrue();
	expect($rule->isValid('VALUE_2'))->toBeTrue();
});

test('validating backed enums', function () {
	$rule = new Enum(SomeBackedEnum::class);

	expect($rule->message())->toBe(sprintf(
		'The value must be a valid enumeration [%s] case',
		SomeBackedEnum::class
	));

	expect($rule->isValid('three'))->toBeFalse();
	expect($rule->isValid('four'))->toBeFalse();
	expect($rule->isValid('one'))->toBeTrue();
	expect($rule->isValid('two'))->toBeTrue();
});

test('enum has to exist', function () {
	$this->expectExceptionObject(new UnexpectedValueException(
		sprintf(
			'The enum parameter must be a valid enum. Was given [%s].',
			'Bob'
		)
	));

	new Enum('Bob');
});
