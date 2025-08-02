<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\HasDateTimeFormat;

/**
 * @internal
 */
final class HasDateTimeFormatTest extends TestCase
{
    public function test_datetime_format(): void
    {
        $rule = new HasDateTimeFormat(format: 'yyyy-MM-dd HH:mm:ss');

        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid(null));
        $this->assertFalse($rule->isValid(''));
        $this->assertFalse($rule->isValid(1));
        $this->assertFalse($rule->isValid('this is not a date'));
        $this->assertTrue($rule->isValid('2024-02-19 00:00:00'));
        $this->assertFalse($rule->isValid('2024-02-19'));
    }

    public function test_datetime_format_with_different_format(): void
    {
        $rule = new HasDateTimeFormat('dd/MM/yyyy');

        $this->assertFalse($rule->isValid('2024-02-19'));
        $this->assertTrue($rule->isValid('19/02/2024'));
    }

    public function test_datetime_native_format(): void
    {
        $rule = new HasDateTimeFormat(format: 'Y-m-d H:i:s');

        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid(null));
        $this->assertFalse($rule->isValid(''));
        $this->assertFalse($rule->isValid(1));
        $this->assertFalse($rule->isValid('this is not a date'));
        $this->assertTrue($rule->isValid('2024-02-19 00:00:00'));
        $this->assertFalse($rule->isValid('2024-02-19'));
    }
}
