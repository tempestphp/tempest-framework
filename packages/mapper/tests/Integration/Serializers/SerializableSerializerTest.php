<?php

namespace Tempest\Mapper\Tests\Integration\Serializers;

use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializers\SerializableSerializer;
use Tempest\Mapper\Tests\Integration\Fixtures\JsonSerializableObject;
use Tempest\Mapper\Tests\Integration\Fixtures\SerializableObject;

final class SerializableSerializerTest extends TestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            ['a'],
            new SerializableSerializer()->serialize(new JsonSerializableObject()),
        );

        $this->assertSame(
            'O:60:"Tempest\Mapper\Tests\Integration\Fixtures\SerializableObject":1:{i:0;s:1:"a";}',
            new SerializableSerializer()->serialize(new SerializableObject()),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(CannotSerializeValue::class);

        new SerializableSerializer()->serialize('foo');
    }
}
