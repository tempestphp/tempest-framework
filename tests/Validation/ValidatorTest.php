<?php

declare(strict_types=1);

use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Validator;
use Tests\Tempest\Validation\Fixtures\ObjectTobeValidated;

test('validator', function () {
    $this->expectException(ValidationException::class);

    $validator = new Validator();

    $validator->validate(new ObjectTobeValidated(name: 'a'));
});
