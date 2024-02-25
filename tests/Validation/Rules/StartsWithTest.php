<?php

declare(strict_types=1);

use Tempest\Validation\Rules\StartsWith;

test('starts with', function () {
    $rule = new StartsWith(needle: 'ab');

    expect($rule->message())->toBe('Value should start with ab');

    expect($rule->isValid('ab'))->toBeTrue();
    expect($rule->isValid('abc'))->toBeTrue();
    expect($rule->isValid('a'))->toBeFalse();
    expect($rule->isValid('3434'))->toBeFalse();
});
