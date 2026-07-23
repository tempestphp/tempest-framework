<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsArray;

/**
 * @internal
 */
final class IsArrayTest extends TestCase
{
    #[Test]
    public function validating_arrays(): void
    {
        $rule = new IsArray();

        $this->assertTrue($rule->isValid([]));
        $this->assertTrue($rule->isValid(['a', 'b']));
        $this->assertTrue($rule->isValid(['key' => 'value']));
        $this->assertFalse($rule->isValid('nope'));
        $this->assertFalse($rule->isValid(1));
        $this->assertFalse($rule->isValid(null));
    }

    #[Test]
    public function validating_with_or_null(): void
    {
        $rule = new IsArray(orNull: true);

        $this->assertTrue($rule->isValid(null));
        $this->assertTrue($rule->isValid([]));
        $this->assertFalse($rule->isValid('nope'));
    }
}
