<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

use function Tempest\DateTime\is_leap_year;

final class IsLeapYearTest extends TestCase
{
    use DateTimeTestTrait;

    #[TestWith([2024, true])]
    #[TestWith([2000, true])]
    #[TestWith([1900, false])]
    #[TestWith([2023, false])]
    #[TestWith([2020, true])]
    #[TestWith([2100, false])]
    #[TestWith([2400, true])]
    #[TestWith([1996, true])]
    #[TestWith([1999, false])]
    public function test_is_leap_year(int $year, bool $expected): void
    {
        $this->assertSame($expected, is_leap_year($year));
    }
}
