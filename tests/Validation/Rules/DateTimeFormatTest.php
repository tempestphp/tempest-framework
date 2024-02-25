<?php

declare(strict_types=1);

use Tempest\Validation\Rules\DateTimeFormat;

test('datetime format', function () {
    $rule = new DateTimeFormat();

    expect($rule->isValid(false))->toBeFalse();
    expect($rule->isValid(null))->toBeFalse();
    expect($rule->isValid(''))->toBeFalse();
    expect($rule->isValid('this is not a date'))->toBeFalse();
    expect($rule->isValid('2024-02-19'))->toBeTrue();
});

test('datetime format with different format', function () {
    $rule = new DateTimeFormat('d/m/Y');

    expect($rule->isValid('2024-02-19'))->toBeFalse();
    expect($rule->isValid('19/02/2024'))->toBeTrue();
});

test('datetime format with integer value', function () {
    $rule = new DateTimeFormat();

    expect($rule->isValid(1))->toBeFalse();
});

test('datetime format message', function () {
    $rule = new DateTimeFormat();

    expect($rule->message())->toBe('Value should be a valid datetime in the format Y-m-d');
});
