<?php

declare(strict_types=1);

use Tempest\Validation\Rules\AlphaNumeric;

test('alphanumeric', function () {
    $rule = new AlphaNumeric();

    expect($rule->message())->toBe('Value should only contain alphanumeric characters');
    expect($rule->isValid('string_123'))->toBeFalse();
    expect($rule->isValid('string123'))->toBeTrue();
    expect($rule->isValid('STRING123'))->toBeTrue();
});
