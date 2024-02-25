<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Validator;

class ValidatorTest extends TestCase
{
    /** @test */
    public function test_validator()
    {
        $this->expectException(ValidationException::class);

        $validator = new Validator();

        $validator->validate(new ObjectTobeValidated(name: 'a'));
    }
}
