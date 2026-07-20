<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializers\IntegerSerializer;

final class IntegerSerializerTest extends TestCase
{
    #[Test]
    public function serialize(): void
    {
        $this->assertSame(
            '1',
            new IntegerSerializer()->serialize(1),
        );
    }

    #[Test]
    public function only_arrays_allowed(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new IntegerSerializer()->serialize('foo');
    }
}
