<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\Timezone;
use Tempest\Validation\Rules\IsBeforeDate;

/**
 * @internal
 */
final class IsBeforeDateTest extends TestCase
{
    #[Test]
    public function exclusive(): void
    {
        $now = DateTime::now();
        $rule = new IsBeforeDate($now);

        $this->assertFalse($rule->isValid($now));
        $this->assertTrue($rule->isValid($now->minusSecond()));
        $this->assertFalse($rule->isValid($now->plusSecond()));
    }

    #[Test]
    public function inclusive(): void
    {
        $now = DateTime::now();
        $rule = new IsBeforeDate($now, inclusive: true);

        $this->assertTrue($rule->isValid($now));
        $this->assertTrue($rule->isValid($now->minusSecond()));
        $this->assertFalse($rule->isValid($now->plusSecond()));
    }

    #[Test]
    public function native_exclusive(): void
    {
        $date = new DateTimeImmutable();
        $rule = new IsBeforeDate($date);

        $this->assertFalse($rule->isValid($date->modify('+1 minute')));
        $this->assertTrue($rule->isValid($date->modify('-1 second')));
        $this->assertFalse($rule->isValid($date));
    }

    #[Test]
    public function native_inclusive(): void
    {
        $date = new DateTimeImmutable();
        $rule = new IsBeforeDate($date, inclusive: true);

        $this->assertFalse($rule->isValid($date->modify('+1 minute')));
        $this->assertTrue($rule->isValid($date->modify('-1 second')));
        $this->assertTrue($rule->isValid($date));
    }

    #[Test]
    public function timezone(): void
    {
        $now = DateTime::now(timezone: Timezone::EUROPE_PARIS);
        $rule = new IsBeforeDate($now->convertToTimezone(Timezone::AMERICA_NEW_YORK), inclusive: false);

        // should still work even with different timezones
        $this->assertFalse($rule->isValid($now));
        $this->assertTrue($rule->isValid($now->minusSecond()));
        $this->assertFalse($rule->isValid($now->plusSecond()));
    }

    #[Test]
    public function native_timezone(): void
    {
        $date = new DateTimeImmutable('now', new DateTimeZone('America/New_York'));
        $rule = new IsBeforeDate($date, inclusive: false);
        $utcDate = new DateTimeImmutable();

        // should still work even with different timezones
        $this->assertTrue($utcDate->format('Y-m-d H:i:s') > $date->format('Y-m-d H:i:s'));
        $this->assertFalse($rule->isValid($utcDate->modify('+1 minute')));
        $this->assertTrue($rule->isValid($utcDate->modify('-1 minute')));
    }
}
