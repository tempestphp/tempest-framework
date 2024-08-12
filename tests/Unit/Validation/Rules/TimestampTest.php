<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Timestamp;

/**
 * @internal
 * @small
 */
class TimestampTest extends TestCase
{
    public function test_timestamp(): void
    {
        $rule = new Timestamp();

        $this->assertTrue($rule->isValid(time()));
        $this->assertFalse($rule->isValid('2021-01-01'));
    }

    public function test_timestamp_message(): void
    {
        $rule = new Timestamp();

        $this->assertSame('Value should be a valid timestamp', $rule->message());
    }
}
