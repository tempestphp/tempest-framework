<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\Meridiem;

final class MeridiemTest extends TestCase
{
    use DateTimeTestTrait;

    #[TestWith([0, Meridiem::ANTE_MERIDIEM])]
    #[TestWith([1, Meridiem::ANTE_MERIDIEM])]
    #[TestWith([11, Meridiem::ANTE_MERIDIEM])]
    #[TestWith([12, Meridiem::POST_MERIDIEM])]
    #[TestWith([23, Meridiem::POST_MERIDIEM])]
    #[TestWith([14, Meridiem::POST_MERIDIEM])]
    #[Test]
    public function from_hour(int $hour, Meridiem $expected): void
    {
        $this->assertSame($expected, Meridiem::fromHour($hour));
    }

    #[Test]
    public function toggle(): void
    {
        $this->assertSame(Meridiem::POST_MERIDIEM, Meridiem::ANTE_MERIDIEM->toggle());
        $this->assertSame(Meridiem::ANTE_MERIDIEM, Meridiem::POST_MERIDIEM->toggle());
    }
}
