<?php

declare(strict_types=1);

namespace Tests\Tempest\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Date;

class DateTest extends TestCase
{
    public function test_date()
    {
        $rule = new Date();

        $this->assertFalse($rule->isValid('this is not a date'));
        $this->assertTrue($rule->isValid('2024-02-19'));
    }

    public function test_date_with_different_format()
    {
        $rule = new Date('d/m/Y');

        $this->assertFalse($rule->isValid('2024-02-19'));
        $this->assertTrue($rule->isValid('19/02/2024'));
    }

    public function test_date_with_integer_value()
    {
        $rule = new Date();

        $this->assertFalse($rule->isValid(1));
    }

    public function test_date_message()
    {
        $rule = new Date();

        $this->assertSame('Value should be a valid date in the format Y-m-d', $rule->message());
    }
}
