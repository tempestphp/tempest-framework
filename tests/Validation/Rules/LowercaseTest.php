<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Lowercase;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('lowercase', function () {
    $rule = new Lowercase();

    expect($rule->message())->toBe('Value should be a lowercase string');

    expect($rule->isValid('abc'))->toBeTrue();
    expect($rule->isValid('àbç'))->toBeTrue();
    expect($rule->isValid('ABC'))->toBeFalse();
    expect($rule->isValid('ÀBÇ'))->toBeFalse();
    expect($rule->isValid('AbC'))->toBeFalse();
});
