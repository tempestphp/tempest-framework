<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Validation\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\Time;

/**
 * @internal
 * @small
 */
class TimeTest extends TestCase
{
    public function test_time(): void
    {
        $rule = new Time();

        $this->assertSame('Value should be a valid time in the format of hh:mm xm', $rule->message());

        $this->assertFalse($rule->isValid('0001'));
        $this->assertFalse($rule->isValid('01:00'));
        $this->assertFalse($rule->isValid('200'));
        $this->assertFalse($rule->isValid('01:60 a.m.'));
        $this->assertFalse($rule->isValid('23:00'));
        $this->assertFalse($rule->isValid('2300'));


        $this->assertTrue($rule->isValid('01:00 am'));
        $this->assertTrue($rule->isValid('01:00 a.m.'));
        $this->assertTrue($rule->isValid('01:00 A.M.'));
        $this->assertTrue($rule->isValid('01:00 AM'));
        $this->assertTrue($rule->isValid('01:00 pm'));
        $this->assertTrue($rule->isValid('01:00 p.m.'));
        $this->assertTrue($rule->isValid('01:00 P.M.'));
        $this->assertTrue($rule->isValid('01:00 PM'));
        $this->assertTrue($rule->isValid('01:59 a.m.'));
    }

    public function test_military_time(): void
    {
        $rule = new Time(twentyFourHour: true);

        $this->assertSame('Value should be a valid time in the 24-hour format of hh:mm', $rule->message());

        $this->assertFalse($rule->isValid('2400'));
        $this->assertFalse($rule->isValid('01:00 am'));
        $this->assertFalse($rule->isValid('01:00 a.m.'));
        $this->assertFalse($rule->isValid('01:00 A.M.'));
        $this->assertFalse($rule->isValid('01:00 AM'));
        $this->assertFalse($rule->isValid('01:00 pm'));
        $this->assertFalse($rule->isValid('01:00 p.m.'));
        $this->assertFalse($rule->isValid('01:00 P.M.'));
        $this->assertFalse($rule->isValid('01:00 PM'));
        $this->assertFalse($rule->isValid('01:59 a.m.'));
        $this->assertFalse($rule->isValid('24:00'));

        $this->assertTrue($rule->isValid('23:00'));
        $this->assertTrue($rule->isValid('2300'));
        $this->assertTrue($rule->isValid('0100'));
        $this->assertTrue($rule->isValid('0200'));
        $this->assertTrue($rule->isValid('0300'));
        $this->assertTrue($rule->isValid('0400'));
        $this->assertTrue($rule->isValid('0500'));
        $this->assertTrue($rule->isValid('0600'));
        $this->assertTrue($rule->isValid('0700'));
        $this->assertTrue($rule->isValid('0800'));
        $this->assertTrue($rule->isValid('0900'));
        $this->assertTrue($rule->isValid('1000'));
        $this->assertTrue($rule->isValid('1100'));
        $this->assertTrue($rule->isValid('1200'));
        $this->assertTrue($rule->isValid('1300'));
        $this->assertTrue($rule->isValid('1400'));
        $this->assertTrue($rule->isValid('1500'));
        $this->assertTrue($rule->isValid('1600'));
        $this->assertTrue($rule->isValid('1700'));
        $this->assertTrue($rule->isValid('1800'));
        $this->assertTrue($rule->isValid('1900'));
        $this->assertTrue($rule->isValid('2000'));
        $this->assertTrue($rule->isValid('2100'));
        $this->assertTrue($rule->isValid('2200'));
        $this->assertTrue($rule->isValid('2300'));
        $this->assertTrue($rule->isValid('2340'));
    }
}
