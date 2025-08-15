<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\PrimaryKey;
use Tempest\Validation\Rules\Exists;

/**
 * @internal
 */
final class ExistsTest extends TestCase
{
    #[Test]
    public function throws_exception_for_table_without_column(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A column must be specified when the table is not a model class.');

        new Exists('random-table');
    }

    #[Test]
    public function returns_false_for_null_or_non_integer_values(): void
    {
        $rule = new Exists(ValidateExistsModel::class);

        $this->assertFalse($rule->isValid(1.5));
        $this->assertFalse($rule->isValid([]));
        $this->assertFalse($rule->isValid(true));
        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid(null));
    }

    #[Test]
    public function can_be_constructed_with_valid_model_class(): void
    {
        $this->assertInstanceOf(Exists::class, new Exists(ValidateExistsModel::class));
    }
}

/** @internal */
final class ValidateExistsModel
{
    public function __construct(
        public PrimaryKey $id,
        public string $name,
    ) {}
}
