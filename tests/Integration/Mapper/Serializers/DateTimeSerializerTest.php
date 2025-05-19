<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use DateTime;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\DateTime as DateTimeDateTime;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializers\DateTimeSerializer;

final class DateTimeSerializerTest extends TestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            '2025-01-01T00:00:00.000Z',
            new DateTimeSerializer()->serialize(new DateTime('2025-01-01 00:00:00')),
        );

        $this->assertSame(
            '2025-01-01',
            new DateTimeSerializer('yyyy-MM-dd')->serialize(new DateTime('2025-01-01 00:00:00')),
        );

        $this->assertSame(
            '2025-01-01T00:00:00.000Z',
            new DateTimeSerializer()->serialize(DateTimeDateTime::parse('2025-01-01 00:00:00')),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(CannotSerializeValue::class);

        new DateTimeSerializer()->serialize('foo');
    }
}
