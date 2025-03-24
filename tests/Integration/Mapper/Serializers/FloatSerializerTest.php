<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializers\FloatSerializer;

final class FloatSerializerTest extends TestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            '0.1',
            new FloatSerializer()->serialize(0.1),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(CannotSerializeValue::class);

        new FloatSerializer()->serialize('foo');
    }
}
