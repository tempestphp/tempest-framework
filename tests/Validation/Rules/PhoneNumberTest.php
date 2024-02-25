<?php

declare(strict_types=1);

use Tempest\Validation\Rules\PhoneNumber;

test('phone number', function () {
    $rule = new PhoneNumber();

    expect($rule->message())->toBe('Value should be a valid phone number');

    expect($rule->isValid('this is not a phone number'))->toBeFalse();
    expect($rule->isValid('john.doe@example.com'))->toBeFalse();
    expect($rule->isValid('+1 (805) 380-4329'))->toBeTrue();
    expect($rule->isValid('+32 0497 88 93 11'))->toBeTrue();

    $rule = new PhoneNumber('US');

    expect($rule->message())->toBe('Value should be a valid US phone number');
    expect($rule->isValid('(805) 380-4329'))->toBeTrue();

    $rule = new PhoneNumber('BE');

    expect($rule->message())->toBe('Value should be a valid BE phone number');
    expect($rule->isValid('0497 88 93 11'))->toBeTrue();
});
