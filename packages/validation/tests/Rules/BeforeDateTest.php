<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Timezone;
use Tempest\Validation\Rules\BeforeDate;

/**
 * @internal
 */
final class BeforeDateTest extends TestCase
{
    public function test_exclusive(): void
    {
        $now = DateTime::now();
        $rule = new BeforeDate($now);

        $this->assertFalse($rule->isValid($now));
        $this->assertTrue($rule->isValid($now->minusSecond()));
        $this->assertFalse($rule->isValid($now->plusSecond()));
    }

    public function test_inclusive(): void
    {
        $now = DateTime::now();
        $rule = new BeforeDate($now, inclusive: true);

        $this->assertTrue($rule->isValid($now));
        $this->assertTrue($rule->isValid($now->minusSecond()));
        $this->assertFalse($rule->isValid($now->plusSecond()));
    }

    public function test_native_exclusive(): void
    {
        $date = new DateTimeImmutable();
        $rule = new BeforeDate($date);

        $this->assertSame('Value must be a date before ' . $this->formatNativeDateTime($date), $rule->message());

        $this->assertFalse($rule->isValid($date->modify('+1 minute')));
        $this->assertTrue($rule->isValid($date->modify('-1 second')));
        $this->assertFalse($rule->isValid($date));
    }

    public function test_native_inclusive(): void
    {
        $date = new DateTimeImmutable();
        $rule = new BeforeDate($date, inclusive: true);

        $this->assertSame('Value must be a date before or equal to ' . $this->formatNativeDateTime($date), $rule->message());

        $this->assertFalse($rule->isValid($date->modify('+1 minute')));
        $this->assertTrue($rule->isValid($date->modify('-1 second')));
        $this->assertTrue($rule->isValid($date));
    }

    public function test_timezone(): void
    {
        $now = DateTime::now(timezone: Timezone::EUROPE_PARIS);
        $rule = new BeforeDate($now->convertToTimezone(Timezone::AMERICA_NEW_YORK), inclusive: false);

        // should still work even with different timezones
        $this->assertFalse($rule->isValid($now));
        $this->assertTrue($rule->isValid($now->minusSecond()));
        $this->assertFalse($rule->isValid($now->plusSecond()));
    }

    public function test_native_timezone(): void
    {
        $date = new DateTimeImmutable('now', new DateTimeZone('America/New_York'));
        $rule = new BeforeDate($date, inclusive: false);
        $utcDate = new DateTimeImmutable();

        // should still work even with different timezones
        $this->assertTrue($utcDate->format('Y-m-d H:i:s') > $date->format('Y-m-d H:i:s'));
        $this->assertFalse($rule->isValid($utcDate->modify('+1 minute')));
        $this->assertTrue($rule->isValid($utcDate->modify('-1 minute')));
    }

    private function formatNativeDateTime(DateTimeImmutable $dateTime): string
    {
        return DateTime::parse($dateTime)->format();
    }
}
