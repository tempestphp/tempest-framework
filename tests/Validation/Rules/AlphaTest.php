<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Alpha;

test('alpha', function () {
    $rule = new Alpha();

    expect($rule->message())->toBe('Value should only contain alphabetic characters');
    expect($rule->isValid('string123'))->toBeFalse();
    expect($rule->isValid('string'))->toBeTrue();
    expect($rule->isValid('STRING'))->toBeTrue();
});
