<?php

declare(strict_types=1);

use Tempest\Validation\Rules\ShouldBeFalse;

test('should be false', function () {
    $rule = new ShouldBeFalse();

    expect($rule->isValid(true))->toBeFalse();
    expect($rule->isValid('true'))->toBeFalse();
    expect($rule->isValid(1))->toBeFalse();
    expect($rule->isValid('1'))->toBeFalse();
    expect($rule->isValid(false))->toBeTrue();
    expect($rule->isValid('false'))->toBeTrue();
    expect($rule->isValid(0))->toBeTrue();
    expect($rule->isValid('0'))->toBeTrue();
});

test('should be false message', function () {
    $rule = new ShouldBeFalse();

    expect($rule->message())->toBe('Value should represent a boolean false value.');
});
