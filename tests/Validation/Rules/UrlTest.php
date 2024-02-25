<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Url;

test('url', function () {
    $rule = new Url();

    expect($rule->isValid('this is not a url'))->toBeFalse();
    expect($rule->isValid('https://https://example.com'))->toBeFalse();
    expect($rule->isValid('https://example.com'))->toBeTrue();
    expect($rule->isValid('http://example.com'))->toBeTrue();
});

test('url with restricted protocols', function () {
    $rule = new Url(['https']);

    expect($rule->isValid('http://example.com'))->toBeFalse();
    expect($rule->isValid('https://example.com'))->toBeTrue();
});

test('url with integer value', function () {
    $rule = new Url();

    expect($rule->isValid(1))->toBeFalse();
});

test('url message', function () {
    $rule = new Url();

    expect($rule->message())->toBe('Value should be a valid URL');
});
