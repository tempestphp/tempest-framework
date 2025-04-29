<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\DateTime\Weekday;

final class WeekdayTest extends TestCase
{
    use DateTimeTestTrait;

    public function test_get_previous(): void
    {
        $this->assertSame(Weekday::MONDAY, Weekday::TUESDAY->getPrevious());
        $this->assertSame(Weekday::TUESDAY, Weekday::WEDNESDAY->getPrevious());
        $this->assertSame(Weekday::WEDNESDAY, Weekday::THURSDAY->getPrevious());
        $this->assertSame(Weekday::THURSDAY, Weekday::FRIDAY->getPrevious());
        $this->assertSame(Weekday::FRIDAY, Weekday::SATURDAY->getPrevious());
        $this->assertSame(Weekday::SATURDAY, Weekday::SUNDAY->getPrevious());
        $this->assertSame(Weekday::SUNDAY, Weekday::MONDAY->getPrevious());
    }

    public function test_get_next(): void
    {
        $this->assertSame(Weekday::TUESDAY, Weekday::MONDAY->getNext());
        $this->assertSame(Weekday::WEDNESDAY, Weekday::TUESDAY->getNext());
        $this->assertSame(Weekday::THURSDAY, Weekday::WEDNESDAY->getNext());
        $this->assertSame(Weekday::FRIDAY, Weekday::THURSDAY->getNext());
        $this->assertSame(Weekday::SATURDAY, Weekday::FRIDAY->getNext());
        $this->assertSame(Weekday::SUNDAY, Weekday::SATURDAY->getNext());
        $this->assertSame(Weekday::MONDAY, Weekday::SUNDAY->getNext());
    }
}
