<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializers\IntegerSerializer;

#[CoversNothing]
final class IntegerSerializerTest extends TestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            '1',
            new IntegerSerializer()->serialize(1),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(CannotSerializeValue::class);

        new IntegerSerializer()->serialize('foo');
    }
}
