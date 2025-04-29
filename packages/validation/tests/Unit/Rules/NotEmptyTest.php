<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\NotEmpty;

/**
 * @internal
 */
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
