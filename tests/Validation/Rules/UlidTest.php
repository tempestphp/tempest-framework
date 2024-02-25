<?php

declare(strict_types=1);

use Tempest\Validation\Rules\Ulid;

test('ulid', function () {
    $rule = new Ulid();

    expect($rule->message())->toBe('Value should be a valid ULID');

    expect($rule->isValid('01FV8CE8P3XVZTVK0S6F05Z5ZA'))->toBeTrue();
    expect($rule->isValid('01fv8ce8p3xvztvk0S6f05z5za'))->toBeTrue();
    expect($rule->isValid('01FV8CE8P3XVZTVK0S6F05Z5ZU'))->toBeFalse();
    // contains invalid character
    expect($rule->isValid('01FV8CE8P3XVZTVK0S6F05'))->toBeFalse();
    // too short
    expect($rule->isValid('01FV8CE8P3XVZTVK0S6F05Z5ZAAAAA'))->toBeFalse();
    // too long
});
