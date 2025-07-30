<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Timestamp;

/**
 * @internal
 */
final class TimestampTest extends TestCase
{
    public function test_timestamp(): void
    {
        $rule = new Timestamp();

        $this->assertTrue($rule->isValid(time()));
        $this->assertFalse($rule->isValid('2021-01-01'));
    }
}
