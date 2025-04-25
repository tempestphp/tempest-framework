<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializers\SerializableSerializer;
use Tempest\Mapper\Serializers\StringSerializer;
use Tests\Tempest\Integration\Mapper\Fixtures\JsonSerializableObject;
use Tests\Tempest\Integration\Mapper\Fixtures\SerializableObject;

#[CoversNothing]
final class SerializableSerializerTest extends TestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            ['a'],
            new SerializableSerializer()->serialize(new JsonSerializableObject()),
        );

        $this->assertSame(
            'O:60:"Tests\Tempest\Integration\Mapper\Fixtures\SerializableObject":1:{i:0;s:1:"a";}',
            new SerializableSerializer()->serialize(new SerializableObject()),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(CannotSerializeValue::class);

        new SerializableSerializer()->serialize('foo');
    }
}
