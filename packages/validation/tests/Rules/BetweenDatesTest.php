<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\DateTime\DateTime;
use Tempest\Validation\Rules\BetweenDates;

/**
 * @internal
 */
final class BetweenDatesTest extends TestCase
{
    public function test_exclusive(): void
    {
        $now = DateTime::now();
        $first = $now->minusMinute();
        $second = $now->plusMinute();

        $rule = new BetweenDates($first, $second);

        $this->assertTrue($rule->isValid($now));
        $this->assertTrue($rule->isValid($now->minusSeconds(59)));
        $this->assertTrue($rule->isValid($now->plusSeconds(59)));

        $this->assertFalse($rule->isValid($now->minusMinute()));
        $this->assertFalse($rule->isValid($now->minusMinutes(2)));

        $this->assertFalse($rule->isValid($now->plusMinute()));
        $this->assertFalse($rule->isValid($now->plusMinutes(2)));
    }

    public function test_inclusive(): void
    {
        $now = DateTime::now();
        $first = $now->minusMinute();
        $second = $now->plusMinute();

        $rule = new BetweenDates($first, $second, inclusive: true);

        $this->assertTrue($rule->isValid($now));
        $this->assertTrue($rule->isValid($now->minusSeconds(59)));
        $this->assertTrue($rule->isValid($now->plusSeconds(59)));

        $this->assertTrue($rule->isValid($now->minusMinute()));
        $this->assertFalse($rule->isValid($now->minusMinutes(2)));

        $this->assertTrue($rule->isValid($now->plusMinute()));
        $this->assertFalse($rule->isValid($now->plusMinutes(2)));
    }
}
