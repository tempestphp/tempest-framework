<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Uppercase;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('uppercase', function () {
    $rule = new Uppercase();

    expect($rule->message())->toBe('Value should be an uppercase string');

    expect($rule->isValid('ABC'))->toBeTrue();
    expect($rule->isValid('ÀBÇ'))->toBeTrue();
    expect($rule->isValid('abc'))->toBeFalse();
    expect($rule->isValid('àbç'))->toBeFalse();
    expect($rule->isValid('AbC'))->toBeFalse();
});
