<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\SecondsStyle;
use Tempest\DateTime\Timestamp;

final class SecondsStyleTest extends TestCase
{
    use DateTimeTestTrait;

    #[DataProvider('provide_from_timestamp_data')]
    public function test_from_timestamp(SecondsStyle $expectedSecondsStyle, Timestamp $timestamp): void
    {
        $this->assertSame($expectedSecondsStyle, SecondsStyle::fromTimestamp($timestamp));
    }

    public static function provide_from_timestamp_data(): array
    {
        return [
            [SecondsStyle::Seconds,      Timestamp::fromParts(0)],
            [SecondsStyle::Milliseconds, Timestamp::fromParts(0, 1000000)],
            [SecondsStyle::Microseconds, Timestamp::fromParts(0, 1000)],
            [SecondsStyle::Nanoseconds,  Timestamp::fromParts(0, 1)],
        ];
    }
}
