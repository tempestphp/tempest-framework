<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsUnixTimestamp;

/**
 * @internal
 */
final class IsUnixTimestampTest extends TestCase
{
    #[Test]
    public function timestamp(): void
    {
        $rule = new IsUnixTimestamp();

        $this->assertTrue($rule->isValid(time()));
        $this->assertFalse($rule->isValid('2021-01-01'));
    }
}
