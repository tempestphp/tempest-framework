<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Exists;
use Tempest\Validation\Tests\Fixtures\ValidateExistsModel;

/**
 * @internal
 */
final class ExistsTest extends TestCase
{
    #[Test]
    public function throws_exception_for_invalid_model_class(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Model NonExistentModel does not exist');

        new Exists('NonExistentModel');
    }

    #[Test]
    public function returns_false_for_null_or_non_integer_values(): void
    {
        $rule = new Exists(ValidateExistsModel::class);

        $this->assertFalse($rule->isValid('string'));
        $this->assertFalse($rule->isValid(1.5));
        $this->assertFalse($rule->isValid([]));
        $this->assertFalse($rule->isValid(true));
        $this->assertFalse($rule->isValid(null));
    }

    #[Test]
    public function returns_correct_error_message(): void
    {
        $rule = new Exists(ValidateExistsModel::class);

        $expectedMessage = sprintf('Record for model %s does not exist', ValidateExistsModel::class);
        $this->assertSame($expectedMessage, $rule->message());
    }

    #[Test]
    public function can_be_constructed_with_valid_model_class(): void
    {
        $rule = new Exists(ValidateExistsModel::class);

        $this->assertInstanceOf(Exists::class, $rule);
        $this->assertStringContainsString(ValidateExistsModel::class, $rule->message());
    }
}
