<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\Era;

final class EraTest extends TestCase
{
    use DateTimeTestTrait;

    #[TestWith([2024, Era::ANNO_DOMINI])]
    #[TestWith([1, Era::ANNO_DOMINI])]
    #[TestWith([-2024, Era::BEFORE_CHRIST])]
    #[TestWith([-1, Era::BEFORE_CHRIST])]
    #[TestWith([0, Era::BEFORE_CHRIST])]
    #[Test]
    public function from_year(int $year, Era $expected): void
    {
        $this->assertSame($expected, Era::fromYear($year));
    }

    #[Test]
    public function toggle(): void
    {
        $this->assertSame(Era::ANNO_DOMINI, Era::BEFORE_CHRIST->toggle());
        $this->assertSame(Era::BEFORE_CHRIST, Era::ANNO_DOMINI->toggle());
    }
}
