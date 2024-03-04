<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Validator;
use Tests\Tempest\Unit\Validation\Fixtures\ObjectTobeValidated;

/**
 * @internal
 * @small
 */
class ValidatorTest extends TestCase
{
    public function test_validator()
    {
        $this->expectException(ValidationException::class);

        $validator = new Validator();

        $validator->validate(new ObjectTobeValidated(name: 'a'));
    }
}
