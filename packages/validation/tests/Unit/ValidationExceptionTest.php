<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Unit;

use PHPUnit\Framework\TestCase;
use stdClass;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\Rule;

/**
 * @internal
 */
final class ValidationExceptionTest extends TestCase
{
    public function test_exception_message(): void
    {
        $this->expectException(ValidationException::class);

        $this->expectExceptionMessage('Value should be a valid email address');

        throw new ValidationException(new stdClass(), [
            'email' => [
                new class() implements Rule {
                    public function isValid(mixed $value): bool
                    {
                        return false;
                    }

                    public function message(): string
                    {
                        return 'Value should be a valid email address';
                    }
                },
            ],
        ]);
    }

    public function test_exception_message_with_multiple_messages(): void
    {
        $this->expectException(ValidationException::class);

        $this->expectExceptionMessage('Value should be a valid email address');
        $this->expectExceptionMessage('Value should praise tempest, old gods from the past and the new gods from the future');

        throw new ValidationException(new stdClass(), [
            'email' => [
                new class() implements Rule {
                    public function isValid(mixed $value): bool
                    {
                        return false;
                    }

                    public function message(): string
                    {
                        return 'Value should be a valid email address';
                    }
                },
                new class() implements Rule {
                    public function isValid(mixed $value): bool
                    {
                        return false;
                    }

                    /** @return string[] */
                    public function message(): array
                    {
                        return [
                            'Value should praise tempest',
                            'old gods from the past',
                            'the new gods from the future',
                        ];
                    }
                },
            ],
        ]);
    }
}
