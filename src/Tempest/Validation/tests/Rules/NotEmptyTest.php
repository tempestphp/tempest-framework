<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\NotEmpty;

/**
 * @internal
 */
#[CoversClass(NotEmpty::class)]
final class NotEmptyTest extends TestCase
{
    public function test_not_empty(): void
    {
        $rule = new NotEmpty();

        $this->assertTrue($rule->isValid('t'));
        $this->assertFalse($rule->isValid(''));
        $this->assertFalse($rule->isValid(1));
    }
}
