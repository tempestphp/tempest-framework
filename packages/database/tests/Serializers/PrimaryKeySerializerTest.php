<?php

namespace Tempest\Database\Tests\Serializers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Serializers\PrimaryKeySerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;

final class PrimaryKeySerializerTest extends TestCase
{
    #[Test]
    public function serializes_pk(): void
    {
        $serializer = new PrimaryKeySerializer();

        $this->assertSame('foo', $serializer->serialize(new PrimaryKey('foo')));
        $this->assertSame(123, $serializer->serialize(new PrimaryKey(123)));
    }

    #[Test]
    public function throws_if_not_pk(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        $serializer = new PrimaryKeySerializer();
        $serializer->serialize('not-a-pk');
    }
}
