<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsUppercase;

/**
 * @internal
 */
final class IsUppercaseTest extends TestCase
{
    #[Test]
    public function uppercase(): void
    {
        $rule = new IsUppercase();

        $this->assertTrue($rule->isValid('ABC'));
        $this->assertTrue($rule->isValid('ÀBÇ'));
        $this->assertFalse($rule->isValid('abc'));
        $this->assertFalse($rule->isValid('àbç'));
        $this->assertFalse($rule->isValid('AbC'));
    }
}
