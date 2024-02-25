<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Email;

test('email', function () {
	$rule = new Email();

	expect($rule->message())->toBe('Value should be a valid email address');
	expect($rule->isValid('this is not an email'))->toBeFalse();
	expect($rule->isValid('jim.halpert@dundermifflinpaper.biz'))->toBeTrue();
});
