<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Exceptions\InvalidValueException;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Rules\Email;
use Tempest\Validation\Validator;
use Tests\Tempest\Integration\Console\Fixtures\ArrayInputCommand;
use Tests\Tempest\Unit\Validation\Fixtures\ObjectToBeValidated;

/**
 * @internal
 * @small
 */
class ValidatorTest extends TestCase
{
    public function test_validate(): void
    {
        $this->expectException(ValidationException::class);

        $validator = new Validator();

        $validator->validate(new ObjectToBeValidated(name: 'a'));
    }

    public function test_validate_value(): void
    {
        $this->expectException(InvalidValueException::class);

        $validator = new Validator();

        $validator->validateValue('a', [new Email()]);
    }
}
