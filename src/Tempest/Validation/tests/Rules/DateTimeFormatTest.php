<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\DateTimeFormat;

/**
 * @internal
 * @small
 */
class DateTimeFormatTest extends TestCase
{
    public function test_datetime_format(): void
    {
        $rule = new DateTimeFormat();

        $this->assertFalse($rule->isValid(false));
        $this->assertFalse($rule->isValid(null));
        $this->assertFalse($rule->isValid(''));
        $this->assertFalse($rule->isValid('this is not a date'));
        $this->assertTrue($rule->isValid('2024-02-19'));
    }

    public function test_datetime_format_with_different_format(): void
    {
        $rule = new DateTimeFormat('d/m/Y');

        $this->assertFalse($rule->isValid('2024-02-19'));
        $this->assertTrue($rule->isValid('19/02/2024'));
    }

    public function test_datetime_format_with_integer_value(): void
    {
        $rule = new DateTimeFormat();

        $this->assertFalse($rule->isValid(1));
    }

    public function test_datetime_format_message(): void
    {
        $rule = new DateTimeFormat();

        $this->assertSame('Value should be a valid datetime in the format Y-m-d', $rule->message());
    }
}
