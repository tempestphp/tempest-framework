<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use DateTime;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\DateTime as DateTimeDateTime;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializers\DateTimeSerializer;

final class DateTimeSerializerTest extends TestCase
{
    #[Test]
    public function serialize(): void
    {
        $this->assertSame(
            '2025-01-01 00:00:00',
            new DateTimeSerializer()->serialize(new DateTime('2025-01-01 00:00:00')),
        );

        $this->assertSame(
            '2025-01-01',
            new DateTimeSerializer('yyyy-MM-dd')->serialize(new DateTime('2025-01-01 00:00:00')),
        );

        $this->assertSame(
            '2025-01-01 00:00:00',
            new DateTimeSerializer()->serialize(DateTimeDateTime::parse('2025-01-01 00:00:00')),
        );
    }

    #[Test]
    public function only_arrays_allowed(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new DateTimeSerializer()->serialize('foo');
    }
}
