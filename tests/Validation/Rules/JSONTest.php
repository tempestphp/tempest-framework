<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Json;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('it returns true for valid json string', function () {
    $rule = new Json();
    expect($rule->isValid('{"test": "test"}'))->toBeTrue();
});

test('it returns false for invalid json string', function () {
    $rule = new Json();
    expect($rule->isValid('{"test": test}'))->toBeFalse();
});

test('it allows to specify depth', function () {
    // Not sure if there is a better way of asserting that a php function was called with a given argument
    $this->expectException(ValueError::class);
    $rule = new Json(depth: 0);
    // we intentionally send something that is not valid
    $rule->isValid('{"test": "test"}');
});

test('it allows to specify flags', function () {
    // Not sure if there is a better way of asserting that a php function was called with a given argument
    $this->expectException(ValueError::class);
    $rule = new Json(flags: 232312312);
    // we intentionally send something that is not valid
    $rule->isValid('{"test": "test"}');
});

test('it returns the proper message', function () {
    $rule = new Json();
    expect($rule->message())->toEqual('Value should be a valid JSON string');
});
