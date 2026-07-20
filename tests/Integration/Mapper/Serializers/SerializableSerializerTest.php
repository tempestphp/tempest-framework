<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializers\SerializableSerializer;
use Tests\Tempest\Integration\Mapper\Fixtures\JsonSerializableObject;
use Tests\Tempest\Integration\Mapper\Fixtures\SerializableObject;

final class SerializableSerializerTest extends TestCase
{
    #[Test]
    public function serialize(): void
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

    #[Test]
    public function only_arrays_allowed(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new SerializableSerializer()->serialize('foo');
    }
}
