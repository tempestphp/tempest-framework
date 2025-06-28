<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use DateTime;
use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializers\NativeDateTimeSerializer;

final class NativeDateTimeSerializerTest extends TestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            '2025-01-01 00:00:00',
            new NativeDateTimeSerializer()->serialize(new DateTime('2025-01-01 00:00:00')),
        );

        $this->assertSame(
            '2025-01-01',
            new NativeDateTimeSerializer('Y-m-d')->serialize(new DateTime('2025-01-01 00:00:00')),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new NativeDateTimeSerializer()->serialize('foo');
    }
}
