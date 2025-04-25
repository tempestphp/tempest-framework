<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializers\BooleanSerializer;

#[CoversNothing]
final class BooleanSerializerTest extends TestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            'true',
            new BooleanSerializer()->serialize(true),
        );

        $this->assertSame(
            'false',
            new BooleanSerializer()->serialize(false),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(CannotSerializeValue::class);

        new BooleanSerializer()->serialize('foo');
    }
}
