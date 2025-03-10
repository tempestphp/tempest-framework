<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Exceptions\InvalidValueException;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Rule;
use Tempest\Validation\Rules\Email;
use Tempest\Validation\Tests\Fixtures\ObjectToBeValidated;
use Tempest\Validation\Validator;

use function Tempest\Support\arr;

/**
 * @internal
 */
final class ValidatorTest extends TestCase
{
    public function test_validate(): void
    {
        $this->expectException(ValidationException::class);

        $validator = new Validator();

        $validator->validateObject(new ObjectToBeValidated(name: 'a'));
    }

    public function test_validate_value(): void
    {
        $this->expectException(InvalidValueException::class);

        $validator = new Validator();

        $validator->validateValue('a', [new Email()]);
    }

    public function test_closure_fails_with_false_response(): void
    {
        $this->expectException(InvalidValueException::class);

        $validator = new Validator();

        $validator->validateValue('a', function (mixed $_) {
            return false;
        });
    }

    public function test_closure_fails_with_string_response(): void
    {
        try {
            $validator = new Validator();
            $validator->validateValue('a', function (mixed $_) {
                return 'I expected b';
            });
        } catch (InvalidValueException $invalidValueException) {
            $messages = arr($invalidValueException->failingRules)->map(fn (Rule $rule) => $rule->message());

            $this->assertCount(1, $messages);
            $this->assertContains('I expected b', $messages);
        }
    }

    public function test_closure_passes_with_null_response(): void
    {
        $validator = new Validator();
        $validator->validateValue('a', function (mixed $_) {
            return null;
        });

        $this->expectNotToPerformAssertions();
    }

    public function test_closure_passes_with_true_response(): void
    {
        $validator = new Validator();
        $validator->validateValue('a', function (mixed $_) {
            return true;
        });

        $this->expectNotToPerformAssertions();
    }

    public function test_closure_passes(): void
    {
        $validator = new Validator();

        $validator->validateValue('a', function (mixed $value) {
            return $value === 'a';
        });

        $validator->validateValue('a', function (mixed $value) {
            if ($value === 'a') {
                return true;
            }

            return false;
        });

        $this->expectNotToPerformAssertions();
    }
}
